<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\ShipmentExceptionController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CargoTrace REST API Routes
|--------------------------------------------------------------------------
|
| JSON REST API routes protected with Laravel Sanctum token authentication
| and policy-based authorization.
|
*/

// Public Authentication Endpoints
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Authenticated Endpoints via Laravel Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    // Shipments
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('api.shipments.index');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('api.shipments.store');
    Route::get('/shipments/{id}', [ShipmentController::class, 'show'])->name('api.shipments.show');
    Route::match(['put', 'patch'], '/shipments/{id}', [ShipmentController::class, 'update'])->name('api.shipments.update');
    Route::delete('/shipments/{id}', [ShipmentController::class, 'destroy'])->name('api.shipments.destroy');

    // Tracking
    Route::get('/shipments/{id}/tracking', [TrackingController::class, 'index'])->name('api.shipments.tracking.index');
    Route::post('/shipments/{id}/tracking', [TrackingController::class, 'store'])->name('api.shipments.tracking.store');

    // Carriers
    Route::get('/carriers', [CarrierController::class, 'index'])->name('api.carriers.index');
    Route::post('/carriers', [CarrierController::class, 'store'])->name('api.carriers.store');

    // Locations
    Route::get('/locations', [LocationController::class, 'index'])->name('api.locations.index');
    Route::post('/locations', [LocationController::class, 'store'])->name('api.locations.store');

    // Exceptions
    Route::get('/shipments/{id}/exceptions', [ShipmentExceptionController::class, 'index'])->name('api.shipments.exceptions.index');
    Route::post('/shipments/{id}/exceptions', [ShipmentExceptionController::class, 'store'])->name('api.shipments.exceptions.store');
    Route::patch('/exceptions/{id}', [ShipmentExceptionController::class, 'update'])->name('api.exceptions.update');
});
