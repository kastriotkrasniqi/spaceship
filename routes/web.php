<?php

use App\Http\Controllers\ProfileController;
use App\Http\Resources\StarshipResource;
use App\Models\Starship;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/users', function () {
    return Inertia::render('Users/Index');
});

Route::get('/', function () {
    $starships = StarshipResource::collection(Starship::all());
    return Inertia::render('dashboard', [
        'starships' => $starships,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
