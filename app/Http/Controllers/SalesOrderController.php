<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Customer;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $salesOrders = SalesOrder::with('customer')->latest()->paginate(10);
        return view('sales-orders.index', compact('salesOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        $newSONumber = 'SO-' . date('Ymd') . '-' . str_pad(SalesOrder::count() + 1, 4, '0', STR_PAD_LEFT);
        return view('sales-orders.form', compact('customers'), ['salesOrder' => new SalesOrder(), 'newSONumber' => $newSONumber]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'so_number' => 'required|string|max:255|unique:sales_orders',
            'order_date' => 'required|date',
            'required_date' => 'nullable|date',
            'status' => 'required|in:draft,confirmed,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $validated['created_by'] = auth()->id();

        SalesOrder::create($validated);

        return redirect()->route('sales-orders.index')->with('success', 'Sales Order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load('customer', 'items');
        return view('sales-orders.show', compact('salesOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesOrder $salesOrder)
    {
        $customers = Customer::all();
        return view('sales-orders.form', compact('customers'), ['salesOrder' => $salesOrder]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'so_number' => 'required|string|max:255|unique:sales_orders,so_number,' . $salesOrder->id,
            'order_date' => 'required|date',
            'required_date' => 'nullable|date',
            'status' => 'required|in:draft,confirmed,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $salesOrder->update($validated);

        return redirect()->route('sales-orders.index')->with('success', 'Sales Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesOrder $salesOrder)
    {
        $salesOrder->delete();
        return redirect()->route('sales-orders.index')->with('success', 'Sales Order deleted successfully.');
    }
}
