<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Mattiverse\Userstamps\Traits\Userstamps;

arch()->preset()->php();
// arch()->preset()->laravel(); https://github.com/pestphp/pest/issues/1525
arch()->preset()->strict();
arch()->preset()->security();

arch('app')
    ->expect('App')
    ->toHaveMethodsDocumented()
    ->toHavePropertiesDocumented();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('models')
    ->expect('App\Models')
    ->toUseTraits([
        SoftDeletes::class,
        Prunable::class,
        Userstamps::class,
        HasUuids::class,
    ]);

arch('user model')
    ->expect(User::class)
    ->toExtend(Illuminate\Foundation\Auth\User::class)
    ->toImplement(MustVerifyEmail::class)
    ->toUseTraits([
        Notifiable::class,
        TwoFactorAuthenticatable::class,
    ]);

arch('listeners')
    ->expect('App\Listeners')
    ->toImplement(ShouldBeEncrypted::class)
    ->toImplement(ShouldQueue::class)
    ->toImplement(ShouldQueueAfterCommit::class);

arch('mailables')
    ->expect('App\Mail')
    ->toImplement(ShouldQueue::class);

arch('notifications')
    ->expect('App\Notifications')
    ->toImplement(ShouldQueue::class);
