<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext\Facades;

use Illuminate\Support\Facades\Facade;
use JuniorFontenele\LaravelAppContext\ContextManager;

/**
 *@method static array all()
 *@method static mixed get(string $key, mixed $default = null)
 *@method static self set(string $key, mixed $value)
 *@method static array build()
 *@method static self clear()
 *@method static self addProvider(\JuniorFontenele\LaravelAppContext\Contracts\ContextProvider $provider)
 *@method static self addChannel(\JuniorFontenele\LaravelAppContext\Contracts\ContextChannel $channel)
 */
class AppContext extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ContextManager::class;
    }
}
