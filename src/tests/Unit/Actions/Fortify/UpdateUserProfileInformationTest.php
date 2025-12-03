<?php

declare(strict_types=1);

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;

it('updates user profile information with valid data', function (): void {
    $user = User::factory()->create();

    $input = [
        'name' => 'Updated Name',
        'email' => $user->email,
    ];

    $updateUserProfileInformation = new UpdateUserProfileInformation();
    $updateUserProfileInformation->update($user, $input);

    $user->refresh();

    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe($user->email);
});
