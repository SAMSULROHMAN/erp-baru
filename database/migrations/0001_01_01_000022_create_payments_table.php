<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('restrict');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('restrict');
            $table->enum('payment_type', ['customer_payment', 'supplier_payment'])->comment('customer_payment=uang masuk, supplier_payment=uang keluar');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'credit_card'])->default('bank_transfer');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('reference_number')->nullable()->comment('invoice_number, po_number, etc');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('payment_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
