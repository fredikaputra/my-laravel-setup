<?php

declare(strict_types=1);

use App\Providers\FortifyServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Fortify;

it('handles different username types in login rate limiter', function (): void {
    $app = app();
    $provider = new FortifyServiceProvider($app);
    $limiter = RateLimiter::limiter('login');
    $request = Request::create('/login', 'POST', [
        Fortify::username() => 'test@example.com',
    ]);

    $provider->boot();

    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class);
});
