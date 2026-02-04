<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('pro_number')->unique();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('quantity');
            $table->integer('quantity_produced')->default(0);
            $table->date('start_date');
            $table->date('scheduled_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('pro_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
