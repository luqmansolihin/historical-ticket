<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketHistoryController;
use Illuminate\Support\Facades\Route;

// Guest Routes (Login Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('tickets.index');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Admin Only User Creation / Registration
    Route::get('/users/create', [AuthController::class, 'showRegister'])->name('users.create');
    Route::post('/users', [AuthController::class, 'register'])->name('users.store');

    Route::get('/tickets/export', [TicketHistoryController::class, 'exportCsv'])->name('tickets.export');
    Route::resource('tickets', TicketHistoryController::class);
});
