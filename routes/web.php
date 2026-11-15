<?php

use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RequestController::class, 'index'])->name('requests.index');
Route::post('/nearby', [RequestController::class, 'setLocation'])->name('requests.location');
Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
