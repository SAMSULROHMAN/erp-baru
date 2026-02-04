<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of sales orders
     */
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'items.product', 'createdBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('so_number', 'like', "%$search%");
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Sales orders retrieved successfully'
        ]);
    }

    /**
     * Store a newly created sales order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'so_number' => 'required|string|unique:sales_orders',
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'required_date' => 'nullable|date',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $so = SalesOrder::create([
                'so_number' => $validated['so_number'],
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'required_date' => $validated['required_date'] ?? null,
                'tax' => $validated['tax'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
                'created_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
            ]);

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $item_subtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $item_subtotal;

                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item_subtotal,
                ]);
            }

            $tax = $validated['tax'] ?? 0;
            $discount = $validated['discount'] ?? 0;
            $so->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + $tax - $discount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $so->load(['customer', 'items.product']),
                'message' => 'Sales order created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified sales order
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.product', 'createdBy', 'invoice']);

        return response()->json([
            'success' => true,
            'data' => $salesOrder,
            'message' => 'Sales order retrieved successfully'
        ]);
    }

    /**
     * Update the specified sales order
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update draft sales orders'
            ], 422);
        }

        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'order_date' => 'sometimes|required|date',
            'required_date' => 'nullable|date',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $salesOrder->update([
                'customer_id' => $validated['customer_id'] ?? $salesOrder->customer_id,
                'order_date' => $validated['order_date'] ?? $salesOrder->order_date,
                'required_date' => $validated['required_date'] ?? $salesOrder->required_date,
                'tax' => $validated['tax'] ?? $salesOrder->tax,
                'discount' => $validated['discount'] ?? $salesOrder->discount,
                'notes' => $validated['notes'] ?? $salesOrder->notes,
            ]);

            if (isset($validated['items'])) {
                $salesOrder->items()->delete();

                $subtotal = 0;
                foreach ($validated['items'] as $item) {
                    $item_subtotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $item_subtotal;

                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item_subtotal,
                    ]);
                }

                $salesOrder->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $salesOrder->tax - $salesOrder->discount,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $salesOrder->load(['customer', 'items.product']),
                'message' => 'Sales order updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm the sales order
     */
    public function confirm(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only confirm draft sales orders'
            ], 422);
        }

        // Check if stock is available
        foreach ($salesOrder->items as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for product: ' . $item->product->name
                ], 422);
            }
        }

        $salesOrder->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'data' => $salesOrder,
            'message' => 'Sales order confirmed successfully'
        ]);
    }

    /**
     * Ship the sales order
     */
    public function ship(Request $request, SalesOrder $salesOrder)
    {
        if (!in_array($salesOrder->status, ['confirmed', 'shipped'])) {
            return response()->json([
                'success' => false,
                'message' => 'Can only ship confirmed or partially shipped sales orders'
            ], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipped_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['items'] as $item) {
                $soItem = SalesOrderItem::findOrFail($item['sales_order_item_id']);

                // Check if quantity to ship is not more than available
                $available = $soItem->quantity - $soItem->quantity_shipped;
                if ($item['quantity'] > $available) {
                    throw new \Exception("Cannot ship {$item['quantity']} units, only {$available} available");
                }

                // Update shipped quantity
                $soItem->update([
                    'quantity_shipped' => $soItem->quantity_shipped + $item['quantity'],
                ]);

                // Update product stock
                $product = $soItem->product;
                $product->update([
                    'stock_quantity' => $product->stock_quantity - $item['quantity'],
                ]);

                // Create stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference_type' => 'sales_order',
                    'reference_number' => $salesOrder->so_number,
                    'created_by' => auth()->id(),
                ]);
            }

            // Check if all items are shipped
            $allShipped = $salesOrder->items()->get()->every(function ($item) {
                return $item->quantity_shipped >= $item->quantity;
            });

            $status = $allShipped ? 'delivered' : 'shipped';

            $salesOrder->update([
                'status' => $status,
                'shipped_date' => $validated['shipped_date'] ?? now()->toDateString(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $salesOrder->load('items'),
                'message' => 'Sales order shipped successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error shipping sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the sales order
     */
    public function cancel(SalesOrder $salesOrder)
    {
        if (in_array($salesOrder->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel ' . $salesOrder->status . ' sales orders'
            ], 422);
        }

        $salesOrder->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'data' => $salesOrder,
            'message' => 'Sales order cancelled successfully'
        ]);
    }

    /**
     * Create invoice from sales order
     */
    public function createInvoice(SalesOrder $salesOrder)
    {
        if ($salesOrder->invoice()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice already exists for this sales order'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate invoice number
            $lastInvoice = Invoice::latest('id')->first();
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(($lastInvoice ? $lastInvoice->id + 1 : 1), 5, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $salesOrder->subtotal,
                'tax' => $salesOrder->tax,
                'discount' => $salesOrder->discount,
                'total' => $salesOrder->total,
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            // Create invoice items from sales order items
            foreach ($salesOrder->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $invoice->load('items'),
                'message' => 'Invoice created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the sales order
     */
    public function destroy(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete draft sales orders'
            ], 422);
        }

        $salesOrder->items()->delete();
        $salesOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sales order deleted successfully'
        ]);
    }
}
