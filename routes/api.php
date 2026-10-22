<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrashController;

Route::post('/inference', [CrashController::class, 'receiveInference']);
Route::get('/pending-accidents', [CrashController::class, 'getPendingAccidents']);
Route::get('/resolve-accident/{id}', [CrashController::class, 'resolveAccident']);
Route::get('/accidents', function() {
    return response()->json(\App\Models\Accident::orderBy('created_at', 'desc')->get());
});
