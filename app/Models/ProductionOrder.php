<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'pro_number',
        'product_id',
        'quantity',
        'quantity_produced',
        'start_date',
        'scheduled_end_date',
        'actual_end_date',
        'status',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'scheduled_end_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProgress()
    {
        if ($this->quantity == 0) {
            return 0;
        }
        return round(($this->quantity_produced / $this->quantity) * 100, 2);
    }

    public function remainingQuantity()
    {
        return $this->quantity - $this->quantity_produced;
    }
}
