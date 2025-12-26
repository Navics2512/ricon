<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::resource('/', DashboardController::class)->only(['index']);
Route::resource('/history', HistoryController::class); 
