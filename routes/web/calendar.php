<?php

use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::resource('calendar', CalendarController::class)
        ->except(['show'])
        ->parameters(['calendar' => 'event']);
});
