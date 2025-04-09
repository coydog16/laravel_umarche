<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use PHPUnit\Architecture\Services\ServiceContainer;
use App\Http\Controllers\Owner\ShopController;
use App\Http\Controllers\Owner\ImageController;


Route::get('/', function () {
    return view('welcome');
});

Route::prefix('shops')->
    middleware('auth:owners')->group(function(){
        Route::get('index', [ShopController::class,'index'])->name('shops.index');
        Route::get('edit/{shop}',[ShopController::class, 'edit'])->name('shops.edit');
        Route::post('update/{shop}',[ShopController::class, 'update'])->name('shops.update');
    });

Route::resource('images', ImageController::class)
->middleware(['auth:owners', 'verified'])->except(['show']); //showは今回は作成しない

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth:owners', 'verified'])->name('dashboard');

Route::middleware('auth:owners')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/ownerAuth.php';
