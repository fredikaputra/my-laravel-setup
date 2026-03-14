<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('may create a session', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test1@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->postJson(route('login.store'), [
            'email' => 'test1@example.com',
            'password' => 'password',
        ]);

    $this->assertAuthenticatedAs($user);
});

it('may create a session with remember me', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test2@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->postJson(route('login.store'), [
            'email' => 'test2@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

    $this->assertAuthenticatedAs($user);
});

it('redirects to two-factor challenge when enabled', function (): void {
    $user = User::factory()->create([
        'email' => 'test3@example.com',
        'password' => Hash::make('password'),
        'two_factor_secret' => encrypt('secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $response = $this->fromRoute('login')
        ->postJson(route('login.store'), [
            'email' => 'test3@example.com',
            'password' => 'password',
        ]);

    $this->assertGuest();
});

it('fails with invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'test4@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->postJson(route('login.store'), [
            'email' => 'test4@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertJsonValidationErrors('email');

    $this->assertGuest();
});

it('requires email', function (): void {
    $response = $this->fromRoute('login')
        ->postJson(route('login.store'), [
            'password' => 'password',
        ]);

    $response->assertJsonValidationErrors('email');
});

it('requires password', function (): void {
    $response = $this->fromRoute('login')
        ->postJson(route('login.store'), [
            'email' => 'test5@example.com',
        ]);

    $response->assertJsonValidationErrors('password');
});

it('may destroy a session', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('logout'));

    $this->assertGuest();
});

it('redirects authenticated users away from login', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('login'));

    $response->assertRedirect('/');
});

it('throttles login attempts after too many failures', function (): void {
    $user = User::factory()->create([
        'email' => 'test6@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make 5 failed login attempts to trigger rate limiting
    for ($i = 0; $i < 5; $i++) {
        $this->fromRoute('login')
            ->postJson(route('login.store'), [
                'email' => 'test6@example.com',
                'password' => 'wrong-password',
            ]);
    }

    // The 6th attempt should be throttled
    $response = $this->fromRoute('login')
        ->postJson(route('login.store'), [
            'email' => 'test6@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertJsonValidationErrors('email');

    expect($response->json('errors.email.0'))->toContain('Too many login attempts');
});

it('clears rate limit after successful login', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test7@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make a few failed attempts
    for ($i = 0; $i < 3; $i++) {
        $this->fromRoute('login')
            ->postJson(route('login.store'), [
                'email' => 'test7@example.com',
                'password' => 'wrong-password',
            ]);
    }

    // Successful login should clear the rate limit
    $response = $this->fromRoute('login')
        ->postJson(route('login.store'), [
            'email' => 'test7@example.com',
            'password' => 'password',
        ]);

    $this->assertAuthenticatedAs($user);
});

it('dispatches lockout event when rate limit is reached', function (): void {
    Event::fake([Lockout::class]);

    $user = User::factory()->create([
        'email' => 'test8@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make 6 failed login attempts to trigger rate limiting and Lockout event
    // The Lockout event fires on the 6th attempt when tooManyAttempts returns true
    for ($i = 0; $i < 6; $i++) {
        $this->fromRoute('login')
            ->postJson(route('login.store'), [
                'email' => 'test8@example.com',
                'password' => 'wrong-password',
            ]);
    }

    Event::assertDispatched(Lockout::class);
});
