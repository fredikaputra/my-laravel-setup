<?php

declare(strict_types=1);

use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEmailResetNotificationController;
use App\Http\Controllers\UserEmailVerificationController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/** @var Illuminate\Routing\Route $homeRoute */
$homeRoute = Route::inertia('/', 'Welcome');
$homeRoute->name('home');

Route::get('/test', function () {
    /** @var User $user */
    $user = User::query()->first();

    Auth::login($user);

    return auth()->user();
});

Route::get('email/verify/{id}/{hash}', [UserEmailVerificationController::class, 'update'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function (): void {
    // User...
    Route::delete('user', [UserController::class, 'destroy'])->name('user.destroy');

    // Session...
    Route::post('logout', [SessionController::class, 'destroy'])
        ->name('logout');
});

Route::middleware('guest')->group(function (): void {
    // User...
    Route::post('register', [UserController::class, 'store'])
        ->name('register.store');

    // Session...
    Route::post('login', [SessionController::class, 'store'])
        ->name('login.store');

    // User Email Reset Notification...
    Route::post('forgot-password', [UserEmailResetNotificationController::class, 'store'])
        ->name('password.email');
});
