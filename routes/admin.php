<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OwnersController;
use PHPUnit\Architecture\Services\ServiceContainer;


Route::get('/', function () {
    return view('welcome');
});

Route::resource('owners', OwnersController::class)
->middleware(['auth:admins', 'verified']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth:admins', 'verified'])->name('dashboard');

Route::middleware('auth:admins')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/adminAuth.php';
