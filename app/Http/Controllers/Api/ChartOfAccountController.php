<?php

namespace App\Http\Controllers\Api;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of chart of accounts
     */
    public function index(Request $request)
    {
        $query = ChartOfAccount::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%$search%")
                  ->orWhere('name', 'like', "%$search%");
        }

        $accounts = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $accounts,
            'message' => 'Chart of accounts retrieved successfully'
        ]);
    }

    /**
     * Store a newly created chart of account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:chart_of_accounts',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'sub_type' => 'nullable|in:current,fixed,other',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $account = ChartOfAccount::create($validated);

        return response()->json([
            'success' => true,
            'data' => $account,
            'message' => 'Chart of account created successfully'
        ], 201);
    }

    /**
     * Display the specified chart of account
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load('journalDetails');

        return response()->json([
            'success' => true,
            'data' => $chartOfAccount,
            'message' => 'Chart of account retrieved successfully'
        ]);
    }

    /**
     * Update the specified chart of account
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $chartOfAccount->update($validated);

        return response()->json([
            'success' => true,
            'data' => $chartOfAccount,
            'message' => 'Chart of account updated successfully'
        ]);
    }

    /**
     * Delete the specified chart of account
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->journalDetails()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete chart of account with journal entries'
            ], 422);
        }

        $chartOfAccount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chart of account deleted successfully'
        ]);
    }

    /**
     * Get balance of chart of account
     */
    public function getBalance(ChartOfAccount $chartOfAccount)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'code' => $chartOfAccount->code,
                'name' => $chartOfAccount->name,
                'type' => $chartOfAccount->type,
                'balance' => $chartOfAccount->balance,
            ],
            'message' => 'Balance retrieved successfully'
        ]);
    }
}
