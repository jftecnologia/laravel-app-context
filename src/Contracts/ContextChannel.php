<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelContext\Contracts;

interface ContextChannel
{
    /**
     * Register the context in the channel
     */
    public function registerContext(array $context): void;
}
