<?php

declare(strict_types=1);

use App\Models\User;

it('may update profile information', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson(route('user-profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@example.com');
});

it('resets email verification when email changes', function (): void {
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson(route('user-profile.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
        ]);

    expect($user->refresh()->email_verified_at)->toBeNull();
});

it('keeps email verification when email stays the same', function (): void {
    $verifiedAt = now();

    $user = User::factory()->create([
        'email' => 'same@example.com',
        'email_verified_at' => $verifiedAt,
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson(route('user-profile.update'), [
            'name' => 'New Name',
            'email' => 'same@example.com',
        ]);

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

it('requires name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->patchJson(route('user-profile.update'), [
            'email' => 'test@example.com',
        ]);

    $response->assertJsonValidationErrors('name');
});

it('requires email', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->patchJson(route('user-profile.update'), [
            'name' => 'Test User',
        ]);

    $response->assertJsonValidationErrors('email');
});

it('requires valid email', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->patchJson(route('user-profile.update'), [
            'name' => 'Test User',
            'email' => 'not-an-email',
        ]);

    $response->assertJsonValidationErrors('email');
});

it('requires unique email except own', function (): void {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->actingAs($user, 'api')
        ->patchJson(route('user-profile.update'), [
            'name' => 'Test User',
            'email' => 'existing@example.com',
        ]);

    $response->assertJsonValidationErrors('email');
});

it('allows keeping same email', function (): void {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson(route('user-profile.update'), [
            'name' => 'Updated Name',
            'email' => 'test@example.com',
        ]);

    $user->refresh();

    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('test@example.com');
});
