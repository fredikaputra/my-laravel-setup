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

final class User extends Authenticatable implements MustVerifyEmail, OAuthenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUuids;
    use Notifiable;
    use Prunable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
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
     * Get the GitHub user from the stored token.
     */
    public function github(): ?\Laravel\Socialite\Contracts\User
    {
        if (! $this->github_token) {
            return null;
        }

        return Socialite::driver('github')->user();
    }

    /**
     * Get the Google user from the stored token.
     */
    public function google(): ?\Laravel\Socialite\Contracts\User
    {
        if (! $this->google_token) {
            return null;
        }

        return Socialite::driver('google')->user();
    }
}
