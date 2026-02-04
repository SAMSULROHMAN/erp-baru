<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('city', 'like', "%$search%");
        }

        $suppliers = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $suppliers,
            'message' => 'Suppliers retrieved successfully'
        ]);
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => 'Supplier created successfully'
        ], 201);
    }

    /**
     * Display the specified supplier
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['purchaseOrders', 'payments']);

        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => 'Supplier retrieved successfully'
        ]);
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'nullable', 'email', Rule::unique('suppliers')->ignore($supplier->id)],
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $supplier->update($validated);

        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => 'Supplier updated successfully'
        ]);
    }

    /**
     * Delete the specified supplier
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete supplier with purchase orders'
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully'
        ]);
    }
}
