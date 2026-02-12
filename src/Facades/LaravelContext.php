<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelContext\Facades;

use Illuminate\Support\Facades\Facade;
use JuniorFontenele\LaravelContext\ContextManager;

/**
 * @method static array all()
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool has(string $key)
 * @method static self set(string $key, mixed $value)
 * @method static self build()
 * @method static self rebuild()
 * @method static self clear()
 * @method static self reset()
 * @method static self clearProviderCache(string $providerClass)
 * @method static self addProvider(\JuniorFontenele\LaravelContext\Contracts\ContextProvider $provider)
 * @method static self addChannel(\JuniorFontenele\LaravelContext\Contracts\ContextChannel $channel)
 *
 * @see ContextManager
 */
class LaravelContext extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ContextManager::class;
    }
}
