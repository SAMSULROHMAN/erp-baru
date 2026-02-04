<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'items.product', 'createdBy', 'salesOrder']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%$search%");
        }

        $invoices = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $invoices,
            'message' => 'Invoices retrieved successfully'
        ]);
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
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

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $tax = $validated['tax'] ?? 0;
            $discount = $validated['discount'] ?? 0;
            $total = $subtotal + $tax - $discount;

            $invoice = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'created_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $item) {
                $item_subtotal = $item['quantity'] * $item['unit_price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item_subtotal,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $invoice->load(['customer', 'items.product']),
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
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'createdBy']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
            'message' => 'Invoice retrieved successfully'
        ]);
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update draft invoices'
            ], 422);
        }

        $validated = $request->validate([
            'invoice_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date',
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

            $invoice->update([
                'invoice_date' => $validated['invoice_date'] ?? $invoice->invoice_date,
                'due_date' => $validated['due_date'] ?? $invoice->due_date,
                'tax' => $validated['tax'] ?? $invoice->tax,
                'discount' => $validated['discount'] ?? $invoice->discount,
                'notes' => $validated['notes'] ?? $invoice->notes,
            ]);

            if (isset($validated['items'])) {
                $invoice->items()->delete();

                $subtotal = 0;
                foreach ($validated['items'] as $item) {
                    $item_subtotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $item_subtotal;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item_subtotal,
                    ]);
                }

                $invoice->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $invoice->tax - $invoice->discount,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $invoice->load(['customer', 'items.product']),
                'message' => 'Invoice updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send the invoice
     */
    public function send(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only send draft invoices'
            ], 422);
        }

        $invoice->update(['status' => 'sent']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
            'message' => 'Invoice sent successfully'
        ]);
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check,credit_card',
            'reference_number' => 'nullable|string',
        ]);

        if ($invoice->getRemainingAmount() <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already fully paid'
            ], 422);
        }

        if ($validated['amount'] > $invoice->getRemainingAmount()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds remaining balance'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $amount = $validated['amount'];
            $invoice->update([
                'amount_paid' => $invoice->amount_paid + $amount,
            ]);

            // Update status if fully paid
            if ($invoice->isPaid()) {
                $invoice->update(['status' => 'paid']);
            }

            // Create payment record
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . str_pad(Payment::latest('id')->first()?->id + 1 ?? 1, 5, '0', STR_PAD_LEFT);

            Payment::create([
                'payment_number' => $paymentNumber,
                'customer_id' => $invoice->customer_id,
                'payment_type' => 'customer_payment',
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'amount' => $amount,
                'reference_number' => $validated['reference_number'] ?? $invoice->invoice_number,
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $invoice,
                'message' => 'Payment recorded successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the invoice
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete draft invoices'
            ], 422);
        }

        $invoice->items()->delete();
        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully'
        ]);
    }
}
