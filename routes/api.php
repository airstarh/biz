<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\HoldController;

Route::prefix('v1')->group(function () {
    Route::get('/slots/availability', [AvailabilityController::class, 'index']);
    Route::post('/slots/{id}/hold', [HoldController::class, 'createHold']);
    Route::post('/holds/{hold}/confirm', [HoldController::class, 'confirmHold']);
    Route::delete('/holds/{hold}', [HoldController::class, 'cancelHold']);
});
