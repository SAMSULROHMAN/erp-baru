<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%$search%")
                  ->orWhere('name', 'like', "%$search%");
        }

        if ($request->has('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'reorder_level');
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Products retrieved successfully'
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:products',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit' => 'required|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'data' => $product->load('category'),
            'message' => 'Product created successfully'
        ], 201);
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product->load(['category', 'stockMovements', 'bomItems']),
            'message' => 'Product retrieved successfully'
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|unique:products,code,' . $product->id,
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'reorder_level' => 'sometimes|required|integer|min:0',
            'unit' => 'sometimes|required|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'data' => $product->load('category'),
            'message' => 'Product updated successfully'
        ]);
    }

    /**
     * Delete the specified product
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        $categories = Category::with('products')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Categories retrieved successfully'
        ]);
    }

    /**
     * Create a new category
     */
    public function createCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Category created successfully'
        ], 201);
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts()
    {
        $products = Product::whereColumn('stock_quantity', '<=', 'reorder_level')
                          ->where('status', 'active')
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Low stock products retrieved successfully'
        ]);
    }
}
