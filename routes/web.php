<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Shipments (Accessible by Admin, Operator, Customer; scoped by Policy)
    Route::resource('shipments', ShipmentController::class);

    // Admin-only management
    Route::middleware('role:admin')->group(function () {
        Route::resource('carriers', CarrierController::class);
        Route::resource('locations', LocationController::class);
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });
});

require __DIR__.'/auth.php';
