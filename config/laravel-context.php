<?php

declare(strict_types = 1);

return [
    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    | Disables or enables the Laravel App Context package
    */
    'enabled' => env('LARAVEL_CONTEXT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    | Providers that collect context information
    | Run in the listed order
    */
    'providers' => [
        JuniorFontenele\LaravelContext\Providers\TimestampProvider::class,
        JuniorFontenele\LaravelContext\Providers\AppProvider::class,
        JuniorFontenele\LaravelContext\Providers\HostProvider::class,
        JuniorFontenele\LaravelContext\Providers\RequestProvider::class,
        JuniorFontenele\LaravelContext\Providers\UserProvider::class,

        // Add your custom providers here
    ],

    /*
    |--------------------------------------------------------------------------
    | Channels
    |--------------------------------------------------------------------------
    | Where the context will be registered
    */
    'channels' => [
        JuniorFontenele\LaravelContext\Channels\LogChannel::class,

        // Add your custom channels here
    ],
];
