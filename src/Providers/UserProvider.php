<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext\Providers;

use Illuminate\Support\Facades\Auth;

class UserProvider extends AbstractProvider
{
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
