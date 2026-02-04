<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber()
    {
        $lastInvoice = \App\Models\Invoice::latest('id')->first();
        $nextId = ($lastInvoice ? $lastInvoice->id + 1 : 1);
        return 'INV-' . date('Ymd') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique payment number
     */
    public static function generatePaymentNumber()
    {
        $lastPayment = \App\Models\Payment::latest('id')->first();
        $nextId = ($lastPayment ? $lastPayment->id + 1 : 1);
        return 'PAY-' . date('Ymd') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique journal number
     */
    public static function generateJournalNumber()
    {
        $lastJournal = \App\Models\Journal::latest('id')->first();
        $nextId = ($lastJournal ? $lastJournal->id + 1 : 1);
        return 'J-' . date('Ymd') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
