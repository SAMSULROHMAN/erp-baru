<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productionOrders = ProductionOrder::with('product')->latest()->paginate(10);
        return view('production-orders.index', compact('productionOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        $newProNumber = 'PRO-' . date('Ymd') . '-' . str_pad(ProductionOrder::count() + 1, 4, '0', STR_PAD_LEFT);
        return view('production-orders.form', compact('products'), ['productionOrder' => new ProductionOrder(), 'newProNumber' => $newProNumber]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'pro_number' => 'required|string|max:255|unique:production_orders',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'scheduled_end_date' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        ProductionOrder::create($validated);

        return redirect()->route('production-orders.index')->with('success', 'Production Order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductionOrder $productionOrder)
    {
        return view('production-orders.index', compact('productionOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductionOrder $productionOrder)
    {
        $products = Product::all();
        return view('production-orders.form', compact('products'), ['productionOrder' => $productionOrder]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductionOrder $productionOrder)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'pro_number' => 'required|string|max:255|unique:production_orders,pro_number,' . $productionOrder->id,
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'scheduled_end_date' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $productionOrder->update($validated);

        return redirect()->route('production-orders.index')->with('success', 'Production Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductionOrder $productionOrder)
    {
        $productionOrder->delete();
        return redirect()->route('production-orders.index')->with('success', 'Production Order deleted successfully.');
    }
}
