<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Response;

final readonly class UserEmailVerificationController
{
    public function update(EmailVerificationRequest $request, #[CurrentUser] User $user): Response
    {
        if ($user->hasVerifiedEmail()) {
            return response()->noContent();
        }

        $request->fulfill();

        return response()->noContent();
    }
}
