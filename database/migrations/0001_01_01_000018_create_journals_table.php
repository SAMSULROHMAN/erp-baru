<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number')->unique();
            $table->enum('type', ['general', 'sales', 'purchase', 'cash'])->default('general');
            $table->date('journal_date');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('posted_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('posted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('journal_number');
            $table->index('status');
            $table->index('journal_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
