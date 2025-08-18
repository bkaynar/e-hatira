<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user && $user->hasRole('user')) {
            return redirect()->route('user.dashboard');
        }
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::resource('packages', PackageController::class);
    });

    Route::middleware(['role:user'])->prefix('user')->name('user.')->group(function () {
        Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
