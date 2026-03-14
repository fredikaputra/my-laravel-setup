<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUserEmailVerificationNotification;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Response;

final readonly class UserEmailVerificationNotificationController
{
    public function store(#[CurrentUser] User $user, CreateUserEmailVerificationNotification $action): Response
    {
        if ($user->hasVerifiedEmail()) {
            return response()->noContent();
        }

        $action->handle($user);

        return response()->noContent();
    }
}
