<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->comment('Produk jadi');
            $table->foreignId('material_product_id')->constrained('products')->onDelete('restrict')->comment('Produk bahan');
            $table->integer('quantity_required');
            $table->string('unit');
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
