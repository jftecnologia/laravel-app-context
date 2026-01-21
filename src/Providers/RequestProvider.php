<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext\Providers;

class RequestProvider extends AbstractProvider
{
    public function shouldRun(): bool
    {
        return ! app()->runningInConsole();
    }

    public function getContext(): array
    {
        return [
            'request' => [
                'ip' => request()->ip(),
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'host' => request()->getHost(),
                'scheme' => request()->getScheme(),
                'locale' => request()->getLocale(),
                'referer' => request()->header('referer'),
                'user_agent' => request()->userAgent(),
                'accept_language' => request()->header('accept-language'),
            ],
        ];
    }
}
