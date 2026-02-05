<?php

namespace App\Http\Controllers;

use App\Models\BomItem;
use App\Models\Product;
use Illuminate\Http\Request;

class BomItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bomItems = BomItem::with(['product', 'materialProduct'])->latest()->paginate(10);
        return view('bom-items.index', compact('bomItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        return view('bom-items.form', compact('products'), ['bomItem' => new BomItem()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'material_product_id' => 'required|exists:products,id',
            'quantity_required' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        BomItem::create($validated);

        return redirect()->route('bom-items.index')->with('success', 'BOM Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BomItem $bomItem)
    {
        return view('bom-items.index', compact('bomItem'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BomItem $bomItem)
    {
        $products = Product::all();
        return view('bom-items.form', compact('products'), ['bomItem' => $bomItem]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BomItem $bomItem)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'material_product_id' => 'required|exists:products,id',
            'quantity_required' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $bomItem->update($validated);

        return redirect()->route('bom-items.index')->with('success', 'BOM Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BomItem $bomItem)
    {
        $bomItem->delete();
        return redirect()->route('bom-items.index')->with('success', 'BOM Item deleted successfully.');
    }
}
