<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventPhotoController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Public event photo upload
Route::get('events/{eventSlug}', [EventPhotoController::class, 'publicUploadPage'])->name('events.public.page');
Route::post('events/{eventSlug}/upload', [EventPhotoController::class, 'publicUpload'])->name('events.public.upload');

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

    // Onboarding routes
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('welcome', [OnboardingController::class, 'welcome'])->name('welcome');
        Route::get('features', [OnboardingController::class, 'features'])->name('features');
        Route::get('packages', [OnboardingController::class, 'packages'])->name('packages');
        Route::get('first-event', [OnboardingController::class, 'firstEvent'])->name('first-event');
        Route::post('complete', [OnboardingController::class, 'complete'])->name('complete');
        Route::post('skip', [OnboardingController::class, 'skip'])->name('skip');
        Route::patch('step', [OnboardingController::class, 'updateStep'])->name('step');
    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::resource('packages', PackageController::class);
        Route::resource('events', EventController::class, ['as' => 'admin']);
        
        Route::prefix('events/{event}/photos')->name('events.photos.')->group(function () {
            Route::post('/', [EventPhotoController::class, 'store'])->name('store');
            Route::patch('{eventPhoto}/approve', [EventPhotoController::class, 'approve'])->name('approve');
            Route::patch('{eventPhoto}/reject', [EventPhotoController::class, 'reject'])->name('reject');
            Route::patch('{eventPhoto}/set-cover', [EventPhotoController::class, 'setCover'])->name('set-cover');
            Route::patch('update-order', [EventPhotoController::class, 'updateOrder'])->name('update-order');
            Route::delete('{eventPhoto}', [EventPhotoController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware(['role:user'])->prefix('user')->name('user.')->group(function () {
        Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');
        Route::patch('complete-onboarding', [UserController::class, 'completeOnboarding'])->name('complete-onboarding');
        Route::resource('events', EventController::class);
        
        Route::prefix('events/{event}/photos')->name('events.photos.')->group(function () {
            Route::post('/', [EventPhotoController::class, 'store'])->name('store');
            Route::patch('{eventPhoto}/approve', [EventPhotoController::class, 'approve'])->name('approve');
            Route::patch('{eventPhoto}/reject', [EventPhotoController::class, 'reject'])->name('reject');
            Route::patch('{eventPhoto}/set-cover', [EventPhotoController::class, 'setCover'])->name('set-cover');
            Route::patch('update-order', [EventPhotoController::class, 'updateOrder'])->name('update-order');
            Route::delete('{eventPhoto}', [EventPhotoController::class, 'destroy'])->name('destroy');
        });
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
