<?php

use App\Http\Controllers\TicketHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('tickets.index');
});

Route::get('/tickets/export', [TicketHistoryController::class, 'exportCsv'])->name('tickets.export');
Route::resource('tickets', TicketHistoryController::class);
