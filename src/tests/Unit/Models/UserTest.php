<?php

declare(strict_types=1);

use App\Models\User;

it('returns users older than one month in prunable query', function (): void {
    $oldUser = User::factory()->create(['created_at' => now()->subMonths(2)]);
    $newUser = User::factory()->create(['created_at' => now()->subWeeks(1)]);

    $prunableQuery = new User()->prunable();
    $prunableUserIds = $prunableQuery->pluck('id');

    expect($prunableUserIds)->toContain($oldUser->id);
    expect($prunableUserIds)->not->toContain($newUser->id);
});

it('returns null for github when no token', function (): void {
    $user = User::factory()->create(['github_token' => null]);

    expect($user->github())->toBeNull();
});

it('returns socialite user for github when token exists', function (): void {
    $user = User::factory()->create(['github_token' => 'fake-token']);

    expect($user->github_token)->toBe('fake-token');
    expect(fn () => $user->github())->not->toThrow('TypeError');
});

it('returns null for google when no token', function (): void {
    $user = User::factory()->create(['google_token' => null]);

    expect($user->google())->toBeNull();
});

it('returns socialite user for google when token exists', function (): void {
    $user = User::factory()->create(['google_token' => 'fake-token']);

    expect($user->google_token)->toBe('fake-token');
    expect(fn () => $user->google())->not->toThrow('TypeError');
});
