<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
