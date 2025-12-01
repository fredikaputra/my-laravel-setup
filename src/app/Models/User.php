<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Laravel\Socialite\Facades\Socialite;
use Mattiverse\Userstamps\Traits\Userstamps;

final class User extends Authenticatable implements MustVerifyEmail, OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable, Prunable, SoftDeletes, TwoFactorAuthenticatable, Userstamps;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'github_token',
        'google_token',
    ];

    /**
     * The attributes that should be visible for serialization.
     *
     * @var list<string>
     */
    protected $visible = [
        'id',
        'name',
        'email',
    ];

    /**
     * Get the prunable model query.
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        return self::query()->where('created_at', '<=', now()->subMonth());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the GitHub user from the stored token.
     */
    public function github(): ?\Laravel\Socialite\Contracts\User
    {
        if (! $this->github_token) {
            return null;
        }

        return Socialite::driver('github')->userFromToken($this->github_token);
    }

    /**
     * Get the GitHub user from the stored token.
     */
    public function google(): ?\Laravel\Socialite\Contracts\User
    {
        if (! $this->google_token) {
            return null;
        }

        return Socialite::driver('google')->userFromToken($this->google_token);
    }
}
