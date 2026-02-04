<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'notes',
        'status',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'supplier_id');
    }
}
