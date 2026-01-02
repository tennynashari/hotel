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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Standard, Deluxe, Suite, dll
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2); // Harga base per malam
            $table->decimal('weekend_price', 12, 2)->nullable(); // Harga weekend
            $table->integer('capacity'); // Kapasitas orang
            $table->json('facilities')->nullable(); // AC, TV, WiFi, dll
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
