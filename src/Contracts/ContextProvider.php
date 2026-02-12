<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelContext\Contracts;

interface ContextProvider
{
    /**
     * Returns an array with context information
     */
    public function getContext(): array;

    /**
     * Indicates if the provider should run
     */
    public function shouldRun(): bool;

    /**
     * Determines if this provider's context should be cached
     * or always recalculated on each request
     *
     * @return bool true if cacheable, false if should always recalculate
     */
    public function isCacheable(): bool;
}
