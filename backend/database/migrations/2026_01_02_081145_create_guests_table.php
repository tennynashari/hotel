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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('id_card_type')->nullable(); // KTP, Passport, dll
            $table->string('id_card_number')->nullable();
            $table->text('address')->nullable();
            $table->string('nationality')->default('Indonesia');
            $table->date('birth_date')->nullable();
            $table->json('preferences')->nullable(); // Preferensi tamu
            $table->boolean('is_repeat_guest')->default(false);
            $table->integer('total_stays')->default(0);
            $table->timestamps();
            
            $table->index('phone');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
