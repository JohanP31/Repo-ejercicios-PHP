<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TipCalculatorController extends Controller
{
    public function index(): View
    {
        return view('tips.index');
    }
}
