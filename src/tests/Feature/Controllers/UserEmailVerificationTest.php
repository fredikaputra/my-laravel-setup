<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\URL;

it('may verify email', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->getKey(), 'hash' => sha1((string) $user->email)]
    );

    $response = $this->actingAs($user)
        ->fromRoute('verification.notice')
        ->get($verificationUrl);

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

it('redirects to dashboard if already verified', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->getKey(), 'hash' => sha1((string) $user->email)]
    );

    $response = $this->actingAs($user)
        ->get($verificationUrl);

    $response->assertNoContent();
});

it('requires valid signature', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $invalidUrl = route('verification.verify', [
        'id' => $user->getKey(),
        'hash' => sha1((string) $user->email),
    ]);

    $response = $this->actingAs($user)
        ->get($invalidUrl);

    $response->assertForbidden();
});

it('requires valid hash', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $invalidUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->getKey(), 'hash' => 'invalid-hash']
    );

    $response = $this->actingAs($user)
        ->get($invalidUrl);

    $response->assertForbidden();
});
