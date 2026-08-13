<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->integer('capacity')->unsigned()->comment('Общая ёмкость слота');
            $table->integer('remaining')->unsigned()->comment('Доступный остаток');
            $table->timestamps();

            // Индексы
            $table->index('remaining');
        });

        // Add constraint to ensure remaining <= capacity
        DB::statement('ALTER TABLE slots ADD CONSTRAINT check_remaining_less_than_capacity CHECK (remaining <= capacity)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE slots DROP CONSTRAINT check_remaining_less_than_capacity');
        Schema::dropIfExists('slots');
    }
};
