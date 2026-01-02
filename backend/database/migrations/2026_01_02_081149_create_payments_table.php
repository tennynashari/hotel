<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('restrict');
            $table->string('payment_number')->unique();
            $table->enum('payment_type', ['deposit', 'partial', 'full', 'refund', 'extra_charge']);
            $table->enum('payment_method', ['cash', 'transfer', 'qris', 'card', 'other']);
            $table->decimal('amount', 12, 2);
            $table->string('reference_number')->nullable(); // Nomor transfer/transaksi
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('payment_number');
            $table->index('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
