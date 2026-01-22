<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext\Providers;

use JuniorFontenele\LaravelAppContext\Contracts\ContextProvider;

abstract class AbstractProvider implements ContextProvider
{
    public function shouldRun(): bool
    {
        return true;
    }

    /**
     * By default, providers are cacheable (static context)
     * Override this method for providers with dynamic context (e.g., user, request)
     */
    public function isCacheable(): bool
    {
        return true;
    }
}
