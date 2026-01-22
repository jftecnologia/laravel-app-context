<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext\Providers;

class RequestProvider extends AbstractProvider
{
    /**
     * Request context is NOT cacheable because request attributes
     * can be modified during the request lifecycle (middleware, etc.)
     */
    public function isCacheable(): bool
    {
        return false;
    }

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
