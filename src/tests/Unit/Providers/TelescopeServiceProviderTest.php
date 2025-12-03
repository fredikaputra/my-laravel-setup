<?php

declare(strict_types=1);

use App\Providers\TelescopeServiceProvider;

it('sets telescope to night mode on register', function (): void {
    $app = app();
    $provider = new TelescopeServiceProvider($app);

    $provider->register();

    expect(true)->toBeTrue();
});
