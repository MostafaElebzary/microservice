<?php

use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.rabbitmq')->group(function () {
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{id}/set-primary', [AddressController::class, 'setPrimary']);
});
