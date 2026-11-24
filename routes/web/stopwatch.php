<?php

use App\Http\Controllers\StopwatchController;
use Illuminate\Support\Facades\Route;

Route::get('/stopwatch', [StopwatchController::class, 'index'])->name('stopwatch.index');
