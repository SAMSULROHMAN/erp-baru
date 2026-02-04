<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductionOrder;
use App\Models\BomItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ProductionOrderController extends Controller
{
    /**
     * Display a listing of production orders
     */
    public function index(Request $request)
    {
        $query = ProductionOrder::with(['product', 'createdBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('pro_number', 'like', "%$search%");
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Production orders retrieved successfully'
        ]);
    }

    /**
     * Store a newly created production order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pro_number' => 'required|string|unique:production_orders',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'scheduled_end_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $po = ProductionOrder::create([
                'pro_number' => $validated['pro_number'],
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'start_date' => $validated['start_date'],
                'scheduled_end_date' => $validated['scheduled_end_date'] ?? null,
                'created_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
            ]);

            // Check if BOM exists for this product
            $bom = BomItem::where('product_id', $validated['product_id'])->get();
            if ($bom->isEmpty()) {
                DB::rollBack();
                $po->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'BOM not found for this product'
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $po->load('product'),
                'message' => 'Production order created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating production order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified production order
     */
    public function show(ProductionOrder $productionOrder)
    {
        $productionOrder->load(['product.bomItems.materialProduct', 'createdBy']);

        return response()->json([
            'success' => true,
            'data' => $productionOrder,
            'message' => 'Production order retrieved successfully'
        ]);
    }

    /**
     * Update the specified production order
     */
    public function update(Request $request, ProductionOrder $productionOrder)
    {
        if (!in_array($productionOrder->status, ['draft', 'scheduled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Can only update draft or scheduled production orders'
            ], 422);
        }

        $validated = $request->validate([
            'quantity' => 'sometimes|required|integer|min:1',
            'start_date' => 'sometimes|required|date',
            'scheduled_end_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $productionOrder->update($validated);

        return response()->json([
            'success' => true,
            'data' => $productionOrder->load('product'),
            'message' => 'Production order updated successfully'
        ]);
    }

    /**
     * Schedule the production order
     */
    public function schedule(ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only schedule draft production orders'
            ], 422);
        }

        $productionOrder->update(['status' => 'scheduled']);

        return response()->json([
            'success' => true,
            'data' => $productionOrder,
            'message' => 'Production order scheduled successfully'
        ]);
    }

    /**
     * Start production
     */
    public function startProduction(ProductionOrder $productionOrder)
    {
        if (!in_array($productionOrder->status, ['draft', 'scheduled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Can only start draft or scheduled production orders'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check if materials are available
            $bom = BomItem::where('product_id', $productionOrder->product_id)->get();
            foreach ($bom as $item) {
                $neededQuantity = $item->quantity_required * $productionOrder->quantity;
                if ($item->materialProduct->stock_quantity < $neededQuantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock for material: ' . $item->materialProduct->name
                    ], 422);
                }
            }

            $productionOrder->update(['status' => 'in_progress']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $productionOrder,
                'message' => 'Production started successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error starting production: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report production result
     */
    public function reportProduction(Request $request, ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Can only report production for in-progress orders'
            ], 422);
        }

        $validated = $request->validate([
            'quantity_produced' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $newQuantity = $productionOrder->quantity_produced + $validated['quantity_produced'];

            if ($newQuantity > $productionOrder->quantity) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Produced quantity exceeds planned quantity'
                ], 422);
            }

            // Deduct materials from stock
            $bom = BomItem::where('product_id', $productionOrder->product_id)->get();
            foreach ($bom as $item) {
                $neededQuantity = $item->quantity_required * $validated['quantity_produced'];
                $product = $item->materialProduct;

                $product->update([
                    'stock_quantity' => $product->stock_quantity - $neededQuantity,
                ]);

                // Create stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $neededQuantity,
                    'reference_type' => 'production',
                    'reference_number' => $productionOrder->pro_number,
                    'notes' => 'Material deduction for production',
                    'created_by' => auth()->id(),
                ]);
            }

            // Add finished product to stock
            $finishedProduct = $productionOrder->product;
            $finishedProduct->update([
                'stock_quantity' => $finishedProduct->stock_quantity + $validated['quantity_produced'],
            ]);

            // Create stock movement for finished product
            StockMovement::create([
                'product_id' => $finishedProduct->id,
                'type' => 'in',
                'quantity' => $validated['quantity_produced'],
                'reference_type' => 'production',
                'reference_number' => $productionOrder->pro_number,
                'notes' => 'Finished product from production',
                'created_by' => auth()->id(),
            ]);

            $productionOrder->update([
                'quantity_produced' => $newQuantity,
            ]);

            // Complete if all quantity produced
            if ($newQuantity >= $productionOrder->quantity) {
                $productionOrder->update([
                    'status' => 'completed',
                    'actual_end_date' => now()->toDateString(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $productionOrder,
                'message' => 'Production result reported successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error reporting production: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete the production order
     */
    public function complete(ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Can only complete in-progress production orders'
            ], 422);
        }

        $productionOrder->update([
            'status' => 'completed',
            'actual_end_date' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $productionOrder,
            'message' => 'Production order completed successfully'
        ]);
    }

    /**
     * Cancel the production order
     */
    public function cancel(ProductionOrder $productionOrder)
    {
        if (in_array($productionOrder->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel ' . $productionOrder->status . ' production orders'
            ], 422);
        }

        $productionOrder->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'data' => $productionOrder,
            'message' => 'Production order cancelled successfully'
        ]);
    }

    /**
     * Delete the production order
     */
    public function destroy(ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete draft production orders'
            ], 422);
        }

        $productionOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Production order deleted successfully'
        ]);
    }
}
