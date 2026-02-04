<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->onDelete('cascade');
            $table->foreignId('chart_of_account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->decimal('debit', 20, 2)->default(0);
            $table->decimal('credit', 20, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable()->comment('purchase_order, sales_order, etc');
            $table->string('reference_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_details');
    }
};
