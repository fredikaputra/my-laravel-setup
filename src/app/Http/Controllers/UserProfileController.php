<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateUser;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Response;

final readonly class UserProfileController
{
    public function update(UpdateUserRequest $request, #[CurrentUser] User $user, UpdateUser $action): Response
    {
        $action->handle($user, $request->validated());

        return response()->noContent();
    }
}
