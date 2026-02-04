<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_number',
        'type',
        'journal_date',
        'description',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
        'notes',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(JournalDetail::class, 'journal_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function getTotalDebit()
    {
        return $this->details()->sum('debit');
    }

    public function getTotalCredit()
    {
        return $this->details()->sum('credit');
    }

    public function isBalanced()
    {
        return $this->getTotalDebit() == $this->getTotalCredit();
    }
}
