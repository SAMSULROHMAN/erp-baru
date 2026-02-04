<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    use HasFactory;

    protected $table = 'bom_items';

    protected $fillable = [
        'product_id',
        'material_product_id',
        'quantity_required',
        'unit',
        'estimated_cost',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function materialProduct()
    {
        return $this->belongsTo(Product::class, 'material_product_id');
    }
}
