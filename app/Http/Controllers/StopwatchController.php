<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StopwatchController extends Controller
{
    public function index(): View
    {
        return view('stopwatch.index');
    }
}
