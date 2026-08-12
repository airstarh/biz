<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['held', 'confirmed', 'cancelled'])->default('held');
            $table->timestamp('expires_at')->comment('Время истечения холда');
            $table->timestamps();

            // Индексы
            $table->index(['slot_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
