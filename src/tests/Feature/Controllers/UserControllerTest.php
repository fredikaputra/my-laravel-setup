<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('may register a new user', function (): void {
    Event::fake([Registered::class]);

    $response = $this->postJson(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ]);

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and(Hash::check('password1234', $user->password))->toBeTrue();

    $this->assertAuthenticatedAs($user);

    Event::assertDispatched(Registered::class);
});

it('requires name', function (): void {
    $response = $this->postJson(route('register.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertJsonValidationErrors('name');
});

it('requires email', function (): void {
    $response = $this->postJson(route('register.store'), [
        'name' => 'Test User',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertJsonValidationErrors('email');
});

it('requires valid email', function (): void {
    $response = $this->postJson(route('register.store'), [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertJsonValidationErrors('email');
});

it('requires unique email', function (): void {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertJsonValidationErrors('email');
});

it('requires password', function (): void {
    $response = $this->postJson(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response->assertJsonValidationErrors('password');
});

it('requires password confirmation', function (): void {
    $response = $this->postJson(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertJsonValidationErrors('password');
});

it('requires matching password confirmation', function (): void {
    $response = $this->postJson(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertJsonValidationErrors('password');
});

it('may delete user account', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)
        ->deleteJson(route('user.destroy'), [
            'password' => 'password',
        ]);

    expect($user->fresh())->toBeNull();

    $this->assertGuest();
});

it('requires password to delete account', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->deleteJson(route('user.destroy'), []);

    $response->assertJsonValidationErrors('password');

    expect($user->fresh())->not->toBeNull();
});

it('requires correct password to delete account', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)
        ->deleteJson(route('user.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response->assertJsonValidationErrors('password');

    expect($user->fresh())->not->toBeNull();
});

it('redirects authenticated users away from registration', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('register'));

    $response->assertRedirect('/');
});
