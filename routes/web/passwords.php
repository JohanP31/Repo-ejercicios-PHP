<?php

use App\Http\Controllers\PasswordGeneratorController;
use Illuminate\Support\Facades\Route;

Route::get('/password-generator', [PasswordGeneratorController::class, 'index'])
    ->name('passwords.index');
