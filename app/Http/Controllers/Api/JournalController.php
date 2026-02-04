<?php

namespace App\Http\Controllers\Api;

use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalDetail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    /**
     * Display a listing of journals
     */
    public function index(Request $request)
    {
        $query = Journal::with(['details.chartOfAccount', 'createdBy', 'postedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from')) {
            $query->where('journal_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('journal_date', '<=', $request->date_to);
        }

        $journals = $query->latest('journal_date')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $journals,
            'message' => 'Journals retrieved successfully'
        ]);
    }

    /**
     * Store a newly created journal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'journal_number' => 'required|string|unique:journals',
            'type' => 'required|in:general,sales,purchase,cash',
            'journal_date' => 'required|date',
            'description' => 'nullable|string',
            'details' => 'required|array|min:2',
            'details.*.chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'details.*.debit' => 'nullable|numeric|min:0',
            'details.*.credit' => 'nullable|numeric|min:0',
            'details.*.description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Validate that each detail has either debit or credit
        foreach ($validated['details'] as $detail) {
            if (empty($detail['debit']) && empty($detail['credit'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Each journal detail must have debit or credit'
                ], 422);
            }

            if (!empty($detail['debit']) && !empty($detail['credit'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Journal detail cannot have both debit and credit'
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $journal = Journal::create([
                'journal_number' => $validated['journal_number'],
                'type' => $validated['type'],
                'journal_date' => $validated['journal_date'],
                'description' => $validated['description'] ?? null,
                'status' => 'draft',
                'created_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($validated['details'] as $detail) {
                $debit = $detail['debit'] ?? 0;
                $credit = $detail['credit'] ?? 0;
                $totalDebit += $debit;
                $totalCredit += $credit;

                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'chart_of_account_id' => $detail['chart_of_account_id'],
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $detail['description'] ?? null,
                ]);
            }

            // Validate that journal is balanced
            if ($totalDebit != $totalCredit) {
                DB::rollBack();
                $journal->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Journal is not balanced. Total debit: ' . $totalDebit . ', Total credit: ' . $totalCredit
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $journal->load('details.chartOfAccount'),
                'message' => 'Journal created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating journal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified journal
     */
    public function show(Journal $journal)
    {
        $journal->load(['details.chartOfAccount', 'createdBy', 'postedBy']);

        return response()->json([
            'success' => true,
            'data' => $journal,
            'message' => 'Journal retrieved successfully'
        ]);
    }

    /**
     * Update the specified journal
     */
    public function update(Request $request, Journal $journal)
    {
        if ($journal->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update draft journals'
            ], 422);
        }

        $validated = $request->validate([
            'journal_date' => 'sometimes|required|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'details' => 'sometimes|array|min:2',
            'details.*.chart_of_account_id' => 'required_with:details|exists:chart_of_accounts,id',
            'details.*.debit' => 'nullable|numeric|min:0',
            'details.*.credit' => 'nullable|numeric|min:0',
            'details.*.description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $journal->update([
                'journal_date' => $validated['journal_date'] ?? $journal->journal_date,
                'description' => $validated['description'] ?? $journal->description,
                'notes' => $validated['notes'] ?? $journal->notes,
            ]);

            if (isset($validated['details'])) {
                $journal->details()->delete();

                $totalDebit = 0;
                $totalCredit = 0;

                foreach ($validated['details'] as $detail) {
                    if (empty($detail['debit']) && empty($detail['credit'])) {
                        throw new \Exception('Each journal detail must have debit or credit');
                    }

                    $debit = $detail['debit'] ?? 0;
                    $credit = $detail['credit'] ?? 0;
                    $totalDebit += $debit;
                    $totalCredit += $credit;

                    JournalDetail::create([
                        'journal_id' => $journal->id,
                        'chart_of_account_id' => $detail['chart_of_account_id'],
                        'debit' => $debit,
                        'credit' => $credit,
                        'description' => $detail['description'] ?? null,
                    ]);
                }

                if ($totalDebit != $totalCredit) {
                    throw new \Exception('Journal is not balanced');
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $journal->load('details.chartOfAccount'),
                'message' => 'Journal updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating journal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Post the journal
     */
    public function post(Journal $journal)
    {
        if ($journal->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only post draft journals'
            ], 422);
        }

        if (!$journal->isBalanced()) {
            return response()->json([
                'success' => false,
                'message' => 'Journal is not balanced'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $journal->update([
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            // Update chart of accounts balance
            foreach ($journal->details as $detail) {
                $coa = $detail->chartOfAccount;
                if ($coa->type === 'asset' || $coa->type === 'expense') {
                    $coa->update([
                        'balance' => $coa->balance + $detail->debit - $detail->credit,
                    ]);
                } else {
                    $coa->update([
                        'balance' => $coa->balance - $detail->debit + $detail->credit,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $journal,
                'message' => 'Journal posted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error posting journal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the journal
     */
    public function destroy(Journal $journal)
    {
        if ($journal->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete draft journals'
            ], 422);
        }

        $journal->details()->delete();
        $journal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Journal deleted successfully'
        ]);
    }
}
