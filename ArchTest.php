<?php

declare(strict_types=1);

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
    ->toHaveAttribute('Illuminate\Database\Eloquent\Attributes\ObservedBy')
    ->toUseTraits([
        'Illuminate\Database\Eloquent\SoftDeletes',
        'Illuminate\Database\Eloquent\Prunable',
        'Mattiverse\Userstamps\Traits\Userstamps',
    ]);

arch('user model')
    ->expect('App\Models\User')
    ->toExtend('Illuminate\Foundation\Auth\User')
    ->toImplement('Illuminate\Contracts\Auth\MustVerifyEmail')
    ->toUseTraits([
        'Illuminate\Notifications\Notifiable',
        'Laravel\Fortify\TwoFactorAuthenticatable',
    ]);

arch('listeners')
    ->expect('App\Listeners')
    ->toImplement('Illuminate\Contracts\Queue\ShouldBeEncrypted')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue')
    ->ignoring('App\Listeners\Log\Console')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueueAfterCommit')
    ->ignoring('App\Listeners\Log\Console');

arch('mailables')
    ->expect('App\Mail')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('notifications')
    ->expect('App\Notifications')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('observers')
    ->expect('App\Observers')
    ->toHaveMethods([
        'retrieved',
        'creating',
        'created',
        'updating',
        'updated',
        'saving',
        'saved',
        'deleting',
        'deleted',
        'trashed',
        'forceDeleting',
        'forceDeleted',
        'restoring',
        'restored',
        'replicating',
    ]);
