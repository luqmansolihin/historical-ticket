<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketHistoryController;
use App\Http\Controllers\UserController;
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

    // Admin Only User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AuthController::class, 'showRegister'])->name('users.create');
    Route::post('/users', [AuthController::class, 'register'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/tickets/export', [TicketHistoryController::class, 'exportCsv'])->name('tickets.export');
    Route::resource('tickets', TicketHistoryController::class);
});
