<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketHistoryController;
use Illuminate\Support\Facades\Route;

// Guest Routes (Login & Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('quick-login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('tickets.index');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/tickets/export', [TicketHistoryController::class, 'exportCsv'])->name('tickets.export');
    Route::resource('tickets', TicketHistoryController::class);
});
