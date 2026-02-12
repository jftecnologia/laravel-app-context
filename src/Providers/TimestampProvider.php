<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelContext\Providers;

class TimestampProvider extends AbstractProvider
{
    /**
     * Timestamp context is NOT cacheable because it changes every moment
     */
    public function isCacheable(): bool
    {
        return false;
    }

    public function getContext(): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
