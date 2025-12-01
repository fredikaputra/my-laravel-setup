<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json('Laravel API'))->name('home');
Route::get('/login', fn () => response()->json(['message' => 'The GET method is not supported for route login. Supported method: POST.'], 405))->name('login');
