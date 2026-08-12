<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\HoldController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api/v1')->group(function () {
    Route::get('/slots/availability', [AvailabilityController::class, 'index']);
    Route::post('/slots/{id}/hold', [HoldController::class, 'createHold']);
    Route::post('/holds/{id}/confirm', [HoldController::class, 'confirmHold']);
    Route::delete('/holds/{id}', [HoldController::class, 'cancelHold']);
});
