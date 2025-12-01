<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        $this->configurePasswordRules();
        $this->configureRateLimiting();
        $this->bootModelsDefaults();
    }

    public function configurePasswordRules(){
        Password::defaults(function () {
            $rule = Password::min(8);

            return app()->environment('production')
                ? $rule->letters()->mixedCase()->numbers()->symbols()->uncompromised()
                : $rule;
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    private function configureRateLimiting(): void
    {
        $limit = fn (Request $request) => Limit::perSecond(30, 30)->by($request->user()?->id ?: $request->ip());

        RateLimiter::for('api', $limit);
        RateLimiter::for('web', $limit);
    }

    private function bootModelsDefaults(): void
    {
        Model::unguard();
    }
}
