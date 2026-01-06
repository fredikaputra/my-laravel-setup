<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Fortify;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(TelescopeServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);

            Telescope::night();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePasswordRules();
        $this->configureRateLimiting();
    }

    /**
     * Configure the password rules for the application.
     */
    public function configurePasswordRules(): void
    {
        Password::defaults(fn () => Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised());
    }

    /**
     * Configure the rate limiters for the application.
     */
    private function configureRateLimiting(): void
    {
        $rateLimitKey = fn (Request $request) => $request->user()?->id ?: $request->ip();

        RateLimiter::for($name = 'static', fn (Request $request): array => [
            Limit::perSecond(10, 10)->by($name.'second'.$rateLimitKey($request)),
            Limit::perMinute(100)->by($name.'minute'.$rateLimitKey($request)),
            Limit::perHour(1000)->by($name.'hour'.$rateLimitKey($request)),
            Limit::perDay(50000)->by($name.'day'.$rateLimitKey($request)),
        ]);
        RateLimiter::for($name = 'resource', fn (Request $request): array => [
            Limit::perSecond(10, 10)->by($name.'second'.$rateLimitKey($request)),
            Limit::perMinute(100)->by($name.'minute'.$rateLimitKey($request)),
            Limit::perHour(3000)->by($name.'hour'.$rateLimitKey($request)),
            Limit::perDay(50000)->by($name.'day'.$rateLimitKey($request)),
        ]);
        RateLimiter::for($name = 'intensive', fn (Request $request): array => [
            Limit::perSecond(10, 10)->by($name.'second'.$rateLimitKey($request)),
            Limit::perMinute(60)->by($name.'minute'.$rateLimitKey($request)),
            Limit::perHour(1000)->by($name.'hour'.$rateLimitKey($request)),
            Limit::perDay(10000)->by($name.'day'.$rateLimitKey($request)),
        ]);
        RateLimiter::for($name = 'mutative', fn (Request $request): array => [
            Limit::perSecond(1, 10)->by($name.'second'.$rateLimitKey($request)),
            Limit::perMinute(30)->by($name.'minute'.$rateLimitKey($request)),
            Limit::perHour(500)->by($name.'hour'.$rateLimitKey($request)),
            Limit::perDay(5000)->by($name.'day'.$rateLimitKey($request)),
        ]);
        RateLimiter::for($name = 'two-factor', fn (Request $request): array => [
            Limit::perSecond(5, 10)->by($name.'second'.$rateLimitKey($request)),
            Limit::perMinute(10)->by($name.'minute'.$rateLimitKey($request)),
            Limit::perHour(50)->by($name.'hour'.$rateLimitKey($request)),
            Limit::perDay(200)->by($name.'day'.$rateLimitKey($request)),
        ]);
        RateLimiter::for($name = 'verification', fn (Request $request): array => [
            Limit::perSecond(1, 10)->by($name.'second'.$rateLimitKey($request)),
            Limit::perMinute(5)->by($name.'minute'.$rateLimitKey($request)),
            Limit::perHour(20)->by($name.'hour'.$rateLimitKey($request)),
            Limit::perDay(50)->by($name.'day'.$rateLimitKey($request)),
        ]);
        RateLimiter::for($name = 'login', function (Request $request) use ($name): array {
            $rateLimitKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return [
                Limit::perSecond(5, 10)->by($name.'second'.$rateLimitKey),
                Limit::perMinute(10)->by($name.'minute'.$rateLimitKey),
                Limit::perHour(50)->by($name.'hour'.$rateLimitKey),
                Limit::perDay(500)->by($name.'day'.$rateLimitKey),
            ];
        });
    }
}
