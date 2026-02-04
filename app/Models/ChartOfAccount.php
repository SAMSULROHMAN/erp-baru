<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'code',
        'name',
        'type',
        'sub_type',
        'balance',
        'description',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function journalDetails()
    {
        return $this->hasMany(JournalDetail::class, 'chart_of_account_id');
    }
}
