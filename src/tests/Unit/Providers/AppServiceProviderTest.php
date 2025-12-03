<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Laravel\Telescope\TelescopeServiceProvider;

it('registers telescope in local environment', function (): void {
    $app = Mockery::mock(Application::class);
    $app->shouldReceive('environment')->with('local')->andReturn(true);
    $app->shouldReceive('register')->with(TelescopeServiceProvider::class)->once();
    $app->shouldReceive('register')->with(App\Providers\TelescopeServiceProvider::class)->once();

    $provider = new AppServiceProvider($app);
    $provider->register();

    expect(true)->toBeTrue();
});
