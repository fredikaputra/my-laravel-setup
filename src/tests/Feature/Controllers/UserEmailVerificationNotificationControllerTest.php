<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

it('redirects verified users to dashboard', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('verification.notice'));

    $response->assertRedirect('/home');
});

it('may send verification notification', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson(route('api.verification.send'));

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('redirects verified users when sending notification', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson(route('api.verification.send'));

    Notification::assertNothingSent();
});
