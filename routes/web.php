<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

include('auth.php');
include('web/sections.php');
include('web/tasks.php');
include('web/tips.php');
include('web/passwords.php');
