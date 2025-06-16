<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Display the landing page
     */
    public function index(): View
    {
        return view('landing.index');
    }
}
