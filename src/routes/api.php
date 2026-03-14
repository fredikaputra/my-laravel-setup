<?php

declare(strict_types=1);
use App\Http\Controllers\UserEmailVerificationNotificationController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function (): void {
    // User Profile...
    Route::redirect('settings', '/settings/profile');
    Route::patch('settings/profile', [UserProfileController::class, 'update'])->name('user-profile.update');

    // User Password...
    Route::put('settings/password', [UserPasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('settings.password.update');

    Route::post('email/verification-notification', [UserEmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('api.verification.send');
});

Route::middleware('guest')->group(function (): void {
    // User Password...
    Route::post('reset-password', [UserPasswordController::class, 'store'])
        ->name('password.store');
});
