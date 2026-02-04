<?php

namespace App\Http\Controllers\Api;

use App\Models\BomItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BomController extends Controller
{
    /**
     * Get BOM for a product
     */
    public function getByProduct($productId)
    {
        $items = BomItem::where('product_id', $productId)
                       ->with(['materialProduct'])
                       ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => 'BOM items retrieved successfully'
        ]);
    }

    /**
     * Add item to BOM
     */
    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'material_product_id' => 'required|exists:products,id',
            'quantity_required' => 'required|integer|min:1',
            'unit' => 'required|string',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        // Check if same item already exists
        $exists = BomItem::where('product_id', $validated['product_id'])
                        ->where('material_product_id', $validated['material_product_id'])
                        ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'BOM item already exists'
            ], 422);
        }

        $bomItem = BomItem::create($validated);

        return response()->json([
            'success' => true,
            'data' => $bomItem->load('materialProduct'),
            'message' => 'BOM item added successfully'
        ], 201);
    }

    /**
     * Update BOM item
     */
    public function updateItem(Request $request, BomItem $bomItem)
    {
        $validated = $request->validate([
            'quantity_required' => 'sometimes|required|integer|min:1',
            'unit' => 'sometimes|required|string',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $bomItem->update($validated);

        return response()->json([
            'success' => true,
            'data' => $bomItem,
            'message' => 'BOM item updated successfully'
        ]);
    }

    /**
     * Delete BOM item
     */
    public function deleteItem(BomItem $bomItem)
    {
        $bomItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'BOM item deleted successfully'
        ]);
    }
}
