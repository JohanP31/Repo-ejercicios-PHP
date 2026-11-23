<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PasswordGeneratorController extends Controller
{
    public function index(): View
    {
        return view('passwords.index');
    }
}
