<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('people', [PersonController::class, 'index'])
    ->middleware(['auth'])
    ->name('people.index');

require __DIR__.'/settings.php';

Route::get('people/create', [PersonController::class, 'create'])
    ->middleware(['auth'])
    ->name('people.create');

Route::post('people', [PersonController::class, 'store'])
    ->middleware(['auth'])
    ->name('people.store');