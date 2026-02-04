<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items.product', 'createdBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('po_number', 'like', "%$search%");
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Purchase orders retrieved successfully'
        ]);
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|unique:purchase_orders',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'tax' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $po = PurchaseOrder::create([
                'po_number' => $validated['po_number'],
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'tax' => $validated['tax'] ?? 0,
                'created_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
            ]);

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $item_subtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $item_subtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item_subtotal,
                ]);
            }

            $po->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + ($validated['tax'] ?? 0),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $po->load(['supplier', 'items.product']),
                'message' => 'Purchase order created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase order
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product', 'createdBy']);

        return response()->json([
            'success' => true,
            'data' => $purchaseOrder,
            'message' => 'Purchase order retrieved successfully'
        ]);
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update draft purchase orders'
            ], 422);
        }

        $validated = $request->validate([
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'order_date' => 'sometimes|required|date',
            'expected_delivery_date' => 'nullable|date',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'] ?? $purchaseOrder->supplier_id,
                'order_date' => $validated['order_date'] ?? $purchaseOrder->order_date,
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? $purchaseOrder->expected_delivery_date,
                'tax' => $validated['tax'] ?? $purchaseOrder->tax,
                'notes' => $validated['notes'] ?? $purchaseOrder->notes,
            ]);

            if (isset($validated['items'])) {
                $purchaseOrder->items()->delete();

                $subtotal = 0;
                foreach ($validated['items'] as $item) {
                    $item_subtotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $item_subtotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item_subtotal,
                    ]);
                }

                $purchaseOrder->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $purchaseOrder->tax,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $purchaseOrder->load(['supplier', 'items.product']),
                'message' => 'Purchase order updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit the purchase order
     */
    public function submit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only submit draft purchase orders'
            ], 422);
        }

        $purchaseOrder->update(['status' => 'submitted']);

        return response()->json([
            'success' => true,
            'data' => $purchaseOrder,
            'message' => 'Purchase order submitted successfully'
        ]);
    }

    /**
     * Receive purchase order items
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot receive cancelled purchase order'
            ], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['items'] as $item) {
                $poItem = PurchaseOrderItem::findOrFail($item['purchase_order_item_id']);

                // Update received quantity
                $poItem->update([
                    'received_quantity' => $poItem->received_quantity + $item['quantity'],
                ]);

                // Update product stock
                $product = $poItem->product;
                $product->update([
                    'stock_quantity' => $product->stock_quantity + $item['quantity'],
                ]);

                // Create stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $item['quantity'],
                    'reference_type' => 'purchase_order',
                    'reference_number' => $purchaseOrder->po_number,
                    'created_by' => auth()->id(),
                ]);
            }

            $purchaseOrder->update([
                'status' => 'received',
                'delivery_date' => $validated['delivery_date'] ?? now()->toDateString(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $purchaseOrder->load('items'),
                'message' => 'Purchase order received successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error receiving purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the purchase order
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel ' . $purchaseOrder->status . ' purchase orders'
            ], 422);
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'data' => $purchaseOrder,
            'message' => 'Purchase order cancelled successfully'
        ]);
    }

    /**
     * Delete the purchase order
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete draft purchase orders'
            ], 422);
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase order deleted successfully'
        ]);
    }
}
