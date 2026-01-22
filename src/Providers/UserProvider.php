<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext\Providers;

use Illuminate\Support\Facades\Auth;

class UserProvider extends AbstractProvider
{
    /**
     * User context is NOT cacheable because authentication state can change
     * during request lifecycle (login, logout, etc.)
     */
    public function isCacheable(): bool
    {
        return false;
    }

    public function shouldRun(): bool
    {
        return Auth::check();
    }

    public function getContext(): array
    {
        return [
            'user' => [
                'id' => Auth::id(),
            ],
        ];
    }
}
