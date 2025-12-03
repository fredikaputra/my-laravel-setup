<?php

declare(strict_types=1);

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Validator;

it('updates user password with valid data', function (): void {
    $user = User::factory()->create();
    $updateUserPassword = new UpdateUserPassword();
    $validator = Mockery::mock(Validator::class);
    $validator->shouldReceive('validateWithBag')->with('updatePassword')->once();

    $input = [
        'current_password' => 'password',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ];

    FacadesValidator::shouldReceive('make')
        ->once()
        ->andReturn($validator);

    $updateUserPassword->update($user, $input);

    $user->refresh();

    expect($user->password)->not->toBeNull();
});
