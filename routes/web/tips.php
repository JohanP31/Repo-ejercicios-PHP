<?php

use App\Http\Controllers\TipCalculatorController;
use Illuminate\Support\Facades\Route;

Route::get('/tip-calculator', [TipCalculatorController::class, 'index'])->name('tips.index');
