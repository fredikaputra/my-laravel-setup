<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

it('may send password reset notification', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->postJson(route('password.email'), [
        'email' => 'test@example.com',
    ]);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('returns generic message for non-existent email', function (): void {
    Notification::fake();

    $response = $this->postJson(route('password.email'), [
        'email' => 'nonexistent@example.com',
    ]);

    Notification::assertNothingSent();
});

it('requires email', function (): void {
    $response = $this->postJson(route('password.email'), []);

    $response->assertJsonValidationErrors('email');
});

it('requires valid email format', function (): void {
    $response = $this->postJson(route('password.email'), [
        'email' => 'not-an-email',
    ]);

    $response->assertJsonValidationErrors('email');
});

it('redirects authenticated users away from forgot password', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('password.request'));

    $response->assertRedirect('/');
});
