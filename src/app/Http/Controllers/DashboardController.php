<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\ScraperPreference;

class DashboardController extends Controller
{
    public function index(Request $request){

        $scrapers = Auth::user()->scraperPreferences()->get();

        return view('dashboard', compact('scrapers'));
    }
}
