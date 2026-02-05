<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $journals = Journal::with('createdBy')->latest()->paginate(10);
        return view('journals.index', compact('journals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $newJournalNumber = 'JRN-' . date('Ymd') . '-' . str_pad(Journal::count() + 1, 4, '0', STR_PAD_LEFT);
        return view('journals.form', ['journal' => new Journal(), 'newJournalNumber' => $newJournalNumber]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'journal_number' => 'required|string|max:255|unique:journals',
            'type' => 'required|in:general,sales,purchase,cash',
            'journal_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,posted,reversed',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        Journal::create($validated);

        return redirect()->route('journals.index')->with('success', 'Journal created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Journal $journal)
    {
        return view('journals.index', compact('journal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Journal $journal)
    {
        return view('journals.form', ['journal' => $journal]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Journal $journal)
    {
        $validated = $request->validate([
            'journal_number' => 'required|string|max:255|unique:journals,journal_number,' . $journal->id,
            'type' => 'required|in:general,sales,purchase,cash',
            'journal_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,posted,reversed',
            'notes' => 'nullable|string',
        ]);

        $journal->update($validated);

        return redirect()->route('journals.index')->with('success', 'Journal updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Journal $journal)
    {
        $journal->delete();
        return redirect()->route('journals.index')->with('success', 'Journal deleted successfully.');
    }
}
