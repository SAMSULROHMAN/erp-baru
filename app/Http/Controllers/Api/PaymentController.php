<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['customer', 'supplier', 'createdBy']);

        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $payments = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments,
            'message' => 'Payments retrieved successfully'
        ]);
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_number' => 'required|string|unique:payments',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'payment_type' => 'required|in:customer_payment,supplier_payment',
            'payment_method' => 'required|in:cash,bank_transfer,check,credit_card',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Validate that either customer_id or supplier_id is provided
        if (empty($validated['customer_id']) && empty($validated['supplier_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Either customer_id or supplier_id must be provided'
            ], 422);
        }

        $payment = Payment::create([
            'payment_number' => $validated['payment_number'],
            'customer_id' => $validated['customer_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'payment_type' => $validated['payment_type'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'],
            'amount' => $validated['amount'],
            'reference_number' => $validated['reference_number'] ?? null,
            'status' => 'pending',
            'created_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $payment,
            'message' => 'Payment created successfully'
        ], 201);
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $payment->load(['customer', 'supplier', 'createdBy']);

        return response()->json([
            'success' => true,
            'data' => $payment,
            'message' => 'Payment retrieved successfully'
        ]);
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update pending payments'
            ], 422);
        }

        $validated = $request->validate([
            'payment_date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'payment_method' => 'sometimes|required|in:cash,bank_transfer,check,credit_card',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        return response()->json([
            'success' => true,
            'data' => $payment,
            'message' => 'Payment updated successfully'
        ]);
    }

    /**
     * Confirm/Complete the payment
     */
    public function confirm(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only confirm pending payments'
            ], 422);
        }

        $payment->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'data' => $payment,
            'message' => 'Payment confirmed successfully'
        ]);
    }

    /**
     * Cancel the payment
     */
    public function cancel(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only cancel pending payments'
            ], 422);
        }

        $payment->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'data' => $payment,
            'message' => 'Payment cancelled successfully'
        ]);
    }

    /**
     * Delete the payment
     */
    public function destroy(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete pending payments'
            ], 422);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully'
        ]);
    }
}
