<?php

declare(strict_types=1);

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;

it('resets user password with valid data', function (): void {
    $user = User::factory()->create();
    $oldPasswordHash = $user->password;
    $resetUserPassword = new ResetUserPassword();

    $input = [
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ];

    $resetUserPassword->reset($user, $input);

    $user->refresh();

    expect($user->password)->not->toBe($oldPasswordHash);
});
