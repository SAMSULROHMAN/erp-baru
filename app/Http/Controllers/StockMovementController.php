<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stockMovements = StockMovement::with(['product', 'createdBy'])->latest()->paginate(10);
        return view('stock-movements.index', compact('stockMovements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        return view('stock-movements.form', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reference_type' => 'nullable|in:purchase_order,sales_order,production,adjustment',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        StockMovement::create($validated);

        // Update product stock
        $product = Product::find($validated['product_id']);
        if ($validated['type'] === 'in') {
            $product->stock_quantity += $validated['quantity'];
        } else {
            $product->stock_quantity -= $validated['quantity'];
        }
        $product->save();

        return redirect()->route('stock-movements.index')->with('success', 'Stock Movement created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockMovement $stockMovement)
    {
        return view('stock-movements.index', compact('stockMovement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockMovement $stockMovement)
    {
        $products = Product::all();
        return view('stock-movements.form', compact('products'), ['stockMovement' => $stockMovement]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockMovement $stockMovement)
    {
        // Note: In a real application, you should reverse the old stock movement before applying the new one
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reference_type' => 'nullable|in:purchase_order,sales_order,production,adjustment',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $stockMovement->update($validated);

        return redirect()->route('stock-movements.index')->with('success', 'Stock Movement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockMovement $stockMovement)
    {
        // Note: In a real application, you should reverse the stock movement before deleting
        $stockMovement->delete();
        return redirect()->route('stock-movements.index')->with('success', 'Stock Movement deleted successfully.');
    }
}
