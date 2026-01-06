<?php

declare(strict_types=1);

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [

    /*
    |--------------------------------------------------------------------------
    | Scramble Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be applied to the Scramble documentation routes.
    | By default, the web middleware group is applied along with rate limiting
    | and restricted access middleware to protect the documentation.
    |
    */

    'middleware' => [
        'web',
        'throttle:static',
        RestrictedDocsAccess::class,
    ],

];
