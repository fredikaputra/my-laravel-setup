<?php

declare(strict_types=1);

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;

it('creates new user with valid data', function (): void {
    $input = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $createNewUser = new CreateNewUser();
    $user = $createNewUser->create($input);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->name->toBe('Test User')
        ->email->toBe('test@example.com');
});
