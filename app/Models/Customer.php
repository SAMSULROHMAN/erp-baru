<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'credit_limit',
        'credit_used',
        'status',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_used' => 'decimal:2',
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function creditAvailable()
    {
        return $this->credit_limit - $this->credit_used;
    }
}
