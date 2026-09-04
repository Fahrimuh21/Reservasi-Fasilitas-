<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Officer\ReservationController;

Route::get('/', function () {
    return view('welcome');
});

// Rute khusus Officer / Petugas
Route::prefix('officer')->name('officer.')->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations/{id}/approve', [ReservationController::class, 'approve']);
    Route::post('/reservations/{id}/reject', [ReservationController::class, 'reject']);
    Route::post('/reservations/{id}/cancel-emergency', [ReservationController::class, 'cancelEmergency']);
});
