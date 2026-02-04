<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->date('order_date');
            $table->date('required_date')->nullable();
            $table->date('shipped_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'shipped', 'delivered', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('so_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
