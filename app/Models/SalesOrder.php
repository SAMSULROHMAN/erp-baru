<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_number',
        'customer_id',
        'order_date',
        'required_date',
        'shipped_date',
        'subtotal',
        'tax',
        'discount',
        'total',
        'amount_paid',
        'status',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'required_date' => 'date',
        'shipped_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'sales_order_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculateTotal()
    {
        $subtotal = $this->items()->sum(\DB::raw('quantity * unit_price'));
        $this->subtotal = $subtotal;
        $this->total = $subtotal + $this->tax - $this->discount;
        return $this;
    }

    public function getRemainingAmount()
    {
        return $this->total - $this->amount_paid;
    }
}
