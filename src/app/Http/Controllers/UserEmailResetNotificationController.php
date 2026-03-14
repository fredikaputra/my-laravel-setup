<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUserEmailResetNotification;
use App\Http\Requests\CreateUserEmailResetNotificationRequest;
use Illuminate\Http\Response;

final readonly class UserEmailResetNotificationController
{
    public function store(
        CreateUserEmailResetNotificationRequest $request,
        CreateUserEmailResetNotification $action
    ): Response {
        $action->handle(['email' => $request->string('email')->value()]);

        return response()->noContent();
    }
}
