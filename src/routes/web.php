<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesForceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScraperController;
use App\Http\Controllers\ScraperPreferenceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('scraper-preferences', ScraperPreferenceController::class);

    Route::post('scrapers/{scraperPreference}/run', [ScraperController::class, 'run'])->name('scraper.run');
    Route::get('scrapers/status', [ScraperController::class, 'status'])->name('scraper.status');

    Route::post('preference/update', [ScraperPreferenceController::class, 'update'])->name('preference.update');
});


Route::get('/test', [SalesForceController::class, 'test']);

require __DIR__.'/auth.php';
