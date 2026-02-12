<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelContext\Providers;

class AppProvider extends AbstractProvider
{
    public function getContext(): array
    {
        return [
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'origin' => app()->runningInConsole() ? 'console' : 'web',
            ],
        ];
    }
}
