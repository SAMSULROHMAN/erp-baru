<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('city', 'like', "%$search%");
        }

        $customers = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $customers,
            'message' => 'Customers retrieved successfully'
        ]);
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer created successfully'
        ], 201);
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        $customer->load(['salesOrders', 'invoices', 'payments']);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer retrieved successfully'
        ]);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'nullable', 'email', Rule::unique('customers')->ignore($customer->id)],
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_used' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer updated successfully'
        ]);
    }

    /**
     * Delete the specified customer
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has sales orders
        if ($customer->salesOrders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete customer with sales orders'
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }

    /**
     * Get customer credit info
     */
    public function creditInfo(Customer $customer)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'credit_limit' => $customer->credit_limit,
                'credit_used' => $customer->credit_used,
                'credit_available' => $customer->creditAvailable(),
            ],
            'message' => 'Credit information retrieved successfully'
        ]);
    }
}
