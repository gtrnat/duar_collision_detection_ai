<?php

use Illuminate\Support\Facades\Route;

// Halaman Admin / Dishub Console
Route::get('/admin', function () {
    return view('dashboard');
});

// Halaman User / HP Korban
Route::get('/user', function () {
    return view('user');
});
