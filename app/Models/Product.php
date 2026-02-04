<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category_id',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'reorder_level',
        'unit',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'product_id');
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class, 'product_id');
    }

    public function bomItems()
    {
        return $this->hasMany(BomItem::class, 'product_id');
    }

    public function bomAsComponent()
    {
        return $this->hasMany(BomItem::class, 'material_product_id');
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'product_id');
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, 'product_id');
    }

    public function isLowStock()
    {
        return $this->stock_quantity <= $this->reorder_level;
    }
}
