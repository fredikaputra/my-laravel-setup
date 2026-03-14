<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('may reset password', function (): void {
    Event::fake([PasswordReset::class]);

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Password::createToken($user);

    $response = $this->fromRoute('password.reset', ['token' => $token])
        ->postJson(route('password.store'), [
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => $token,
        ]);

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();

    Event::assertDispatched(PasswordReset::class);
});

it('fails with invalid token', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->fromRoute('password.reset', ['token' => 'invalid-token'])
        ->postJson(route('password.store'), [
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => 'invalid-token',
        ]);

    $response
        ->assertJsonValidationErrors('email');
});

it('fails with non-existent email', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->postJson(route('password.store'), [
            'email' => 'nonexistent@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => 'fake-token',
        ]);

    $response
        ->assertJsonValidationErrors('email');
});

it('requires email', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->postJson(route('password.store'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => 'fake-token',
        ]);

    $response
        ->assertJsonValidationErrors('email');
});

it('requires password', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->postJson(route('password.store'), [
            'email' => 'test@example.com',
            'token' => 'fake-token',
        ]);

    $response
        ->assertJsonValidationErrors('password');
});

it('requires password confirmation', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->postJson(route('password.store'), [
            'email' => 'test@example.com',
            'password' => 'new-password',
            'token' => 'fake-token',
        ]);

    $response
        ->assertJsonValidationErrors('password');
});

it('requires matching password confirmation', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->postJson(route('password.store'), [
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
            'token' => 'fake-token',
        ]);

    $response
        ->assertJsonValidationErrors('password');
});

it('may update password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user, 'api')
        ->putJson(route('settings.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

it('requires current password to update', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->putJson(route('settings.password.update'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertJsonValidationErrors('current_password');
});

it('requires correct current password to update', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user, 'api')
        ->putJson(route('settings.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertJsonValidationErrors('current_password');
});

it('requires new password to update', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user, 'api')
        ->putJson(route('settings.password.update'), [
            'current_password' => 'old-password',
        ]);

    $response->assertJsonValidationErrors('password');
});

it('redirects authenticated users away from reset password', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('password.reset', ['token' => 'fake-token']));

    $response->assertRedirect('/');
});
