# Laravel App Context

[![Latest Version on Packagist](https://img.shields.io/packagist/v/juniorfontenele/laravel-app-context.svg?style=flat-square)](https://packagist.org/packages/juniorfontenele/laravel-app-context)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/juniorfontenele/laravel-app-context/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/juniorfontenele/laravel-app-context/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/juniorfontenele/laravel-app-context/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/juniorfontenele/laravel-app-context/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/juniorfontenele/laravel-app-context.svg?style=flat-square)](https://packagist.org/packages/juniorfontenele/laravel-app-context)

A powerful and extensible package for managing application context in Laravel. Automatically collect and distribute context information from multiple sources (user, request, environment, etc.) to various channels (logs, monitoring systems, etc.).

## Features

- **Automatic Context Collection**: Built-in providers for timestamp, app info, host info, request data, and user information
- **Smart Caching**: Intelligent per-provider caching - static context is cached for performance, dynamic context (user, request) is always fresh
- **Extensible Architecture**: Easy to create custom providers and channels
- **Conditional Execution**: Providers can determine when they should run
- **Multiple Channels**: Register context in different systems (logs, monitoring, etc.)
- **Facade Support**: Clean and elegant API using Laravel facades
- **Configuration-Based**: Manage providers and channels through a simple config file
- **Performance Optimized**: Selective caching ensures optimal performance without stale data

## Installation

You can install the package via composer:

```bash
composer require juniorfontenele/laravel-app-context
```

The package will automatically register its service provider.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-app-context-config"
```

This will create a `config/app-context.php` file with the following structure:

```php
return [
    'enabled' => env('LARAVEL_APP_CONTEXT_ENABLED', true),
    
    'providers' => [
        // Built-in providers
        JuniorFontenele\LaravelAppContext\Providers\TimestampProvider::class,
        JuniorFontenele\LaravelAppContext\Providers\AppProvider::class,
        JuniorFontenele\LaravelAppContext\Providers\HostProvider::class,
        JuniorFontenele\LaravelAppContext\Providers\RequestProvider::class,
        JuniorFontenele\LaravelAppContext\Providers\UserProvider::class,
    ],
    
    'channels' => [
        JuniorFontenele\LaravelAppContext\Channels\LogChannel::class,
        
        // Add your custom channels here
    ],
];
```

## Basic Usage

### Using the Facade

```php
use JuniorFontenele\LaravelAppContext\Facades\AppContext;

// Get all context
$context = AppContext::all();

// Get a specific context value
$userId = AppContext::get('user.id');
$appName = AppContext::get('app.name');

// Get with a default value
$userName = AppContext::get('user.name', 'Guest');

// Check if a key exists
if (AppContext::has('user.id')) {
    // User is authenticated
}

// Set a custom value
AppContext::set('custom.key', 'custom value');

// Force recalculation of all context (clears all caches)
AppContext::refresh();

// Clear cache for a specific provider
use JuniorFontenele\LaravelAppContext\Providers\TimestampProvider;
AppContext::clearProviderCache(TimestampProvider::class);

// Clear the context
AppContext::clear();
```

### Context Structure

The default context includes:

```php
[
    'timestamp' => '2024-01-21T10:30:00+00:00',
    'app' => [
        'name' => 'Laravel',
        'env' => 'production',
        'debug' => false,
        'url' => 'https://example.com',
        'timezone' => 'UTC',
        'locale' => 'en',
        'origin' => 'web', // or 'console'
    ],
    'host' => [
        'name' => 'server-01',
        'ip' => '192.168.1.100',
    ],
    'request' => [ // Only available in web requests
        'ip' => '192.168.1.1',
        'method' => 'GET',
        'url' => 'https://example.com/api/users',
        'host' => 'example.com',
        'scheme' => 'https',
        'locale' => 'en',
        'referer' => 'https://example.com',
        'user_agent' => 'Mozilla/5.0...',
        'accept_language' => 'en-US,en;q=0.9',
    ],
    'user' => [ // Only when authenticated (always up-to-date)
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ],
]
```

> **Note**: User, request and timestamp contexts are **always fresh** and automatically updated when authentication state or request data changes. Static contexts (app, host) are cached for optimal performance.
```

## Smart Caching System

The package uses an intelligent per-provider caching system:

### Cacheable Providers (Static Context)

Providers that return static data are cached for optimal performance:
- `AppProvider` - Application configuration doesn't change
- `HostProvider` - Host information is static

These providers execute **once per request** and their results are cached.

### Dynamic Providers (Always Fresh)

Providers with dynamic data are **never cached** and always recalculated:
- `TimestampProvider` - Timestamp can change during request lifecycle
- `UserProvider` - Authentication state can change (login/logout)
- `RequestProvider` - Request data can be modified by middlewares

These providers execute **every time** you access the context, ensuring data is always up-to-date.

### Example: Login/Logout Scenario

```php
use JuniorFontenele\LaravelAppContext\Facades\AppContext;
use Illuminate\Support\Facades\Auth;

// Before login
$context = AppContext::all();
// Result: no 'user' key (not authenticated)

// User logs in
Auth::login($user);

// After login - NO need to call refresh()!
$context = AppContext::all();
// Result: 'user' key is present with fresh data
// 'app' and 'host' are from cache (fast!)
```

No manual cache management needed! The context stays fresh where it matters while maintaining optimal performance.

## Creating Custom Providers

Providers are classes that collect specific context information. Create a custom provider by implementing the `ContextProvider` interface or extending `AbstractProvider`:

### Basic Provider (Cacheable)

```php
<?php

namespace App\Context\Providers;

use JuniorFontenele\LaravelAppContext\Providers\AbstractProvider;

class CustomProvider extends AbstractProvider
{
    // isCacheable() returns true by default (from AbstractProvider)
    // This means the context will be calculated once and cached
    
    public function getContext(): array
    {
        return [
            'custom' => [
                'key' => 'value',
                'data' => $this->getCustomData(),
            ],
        ];
    }
    
    private function getCustomData(): array
    {
        return [
            'foo' => 'bar',
        ];
    }
}
```

### Dynamic Provider (Non-Cacheable)

For providers with dynamic data that should always be recalculated:

```php
<?php

namespace App\Context\Providers;

use JuniorFontenele\LaravelAppContext\Providers\AbstractProvider;

class SessionProvider extends AbstractProvider
{
    /**
     * Session data can change during the request,
     * so it should not be cached
     */
    public function isCacheable(): bool
    {
        return false; // Always recalculate
    }
    
    public function shouldRun(): bool
    {
        return session()->isStarted();
    }
    
    public function getContext(): array
    {
        return [
            'session' => [
                'id' => session()->getId(),
                'cart_items_count' => count(session()->get('cart', [])),
            ],
        ];
    }
}
```

### Choosing Between Cacheable and Non-Cacheable

**Use cacheable (default)** when:
- ✅ Data is static during the request lifecycle
- ✅ Data comes from configuration files
- ✅ Expensive operations that should run once
- ✅ Environment or system information

**Use non-cacheable** when:
- ❌ Data depends on authentication state
- ❌ Data can be modified during the request
- ❌ Data comes from sessions or request
- ❌ Data changes based on middleware execution

### Conditional Provider

Control when your provider should run using the `shouldRun()` method:

```php
<?php

namespace App\Context\Providers;

use JuniorFontenele\LaravelAppContext\Providers\AbstractProvider;

class DatabaseProvider extends AbstractProvider
{
    public function shouldRun(): bool
    {
        // Only run if database is connected
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getContext(): array
    {
        return [
            'database' => [
                'connection' => config('database.default'),
                'name' => config('database.connections.' . config('database.default') . '.database'),
            ],
        ];
    }
}
```

### Registering Custom Providers

Add your custom provider to the `config/app-context.php` file:

```php
'providers' => [
    // Built-in providers
    JuniorFontenele\LaravelAppContext\Providers\TimestampProvider::class,
    JuniorFontenele\LaravelAppContext\Providers\AppProvider::class,
    
    // Your custom providers
    App\Context\Providers\CustomProvider::class,
    App\Context\Providers\DatabaseProvider::class,
],
```

Or register programmatically in a service provider:

```php
use JuniorFontenele\LaravelAppContext\Facades\AppContext;
use App\Context\Providers\CustomProvider;

public function boot()
{
    AppContext::addProvider(new CustomProvider());
}
```

## Creating Custom Channels

Channels receive the resolved context and register it in different systems (logs, error tracking, etc.). They don't dispatch the context immediately, but rather add it to systems that will use it later. Create a custom channel by implementing the `ContextChannel` interface:

### Basic Channel

```php
<?php

namespace App\Context\Channels;

use JuniorFontenele\LaravelAppContext\Contracts\ContextChannel;
use Illuminate\Support\Facades\Cache;

class CacheChannel implements ContextChannel
{
    public function registerContext(array $context): void
    {
        // Register context in cache for later use
        Cache::put('app.context', $context, now()->addMinutes(5));
    }
}
```

### Advanced Channel Example

```php
<?php

namespace App\Context\Channels;

use JuniorFontenele\LaravelAppContext\Contracts\ContextChannel;
use Sentry\State\Scope;

class SentryChannel implements ContextChannel
{
    public function registerContext(array $context): void
    {
        // Register context in Sentry for error tracking
        // This context will be included in all Sentry error reports
        \Sentry\configureScope(function (Scope $scope) use ($context) {
            $scope->setContext('app', $context['app'] ?? []);
            $scope->setContext('host', $context['host'] ?? []);
            $scope->setContext('request', $context['request'] ?? []);
            
            if (isset($context['user'])) {
                $scope->setUser([
                    'id' => $context['user']['id'],
                    'email' => $context['user']['email'],
                    'username' => $context['user']['name'],
                ]);
            }
        });
    }
}
```

### Registering Custom Channels

Add your custom channel to the `config/app-context.php` file:

```php
'channels' => [
    // Built-in channels
    JuniorFontenele\LaravelAppContext\Channels\LogChannel::class,

    // Add your custom channels here
    App\Context\Channels\SentryChannel::class,
    App\Context\Channels\CacheChannel::class,
],
```

Or register programmatically:

```php
use JuniorFontenele\LaravelAppContext\Facades\AppContext;
use App\Context\Channels\SentryChannel;

public function boot()
{
    AppContext::addChannel(new SentryChannel());
}
```

## API Reference

### Core Methods

```php
use JuniorFontenele\LaravelAppContext\Facades\AppContext;

// Get all context
AppContext::all(): array

// Get a specific value
AppContext::get(string $key, mixed $default = null): mixed

// Check if a key exists
AppContext::has(string $key): bool

// Set a custom value
AppContext::set(string $key, mixed $value): self

// Force complete recalculation (clears all caches)
AppContext::refresh(): self

// Clear cache for specific provider
AppContext::clearProviderCache(string $providerClass): self

// Clear all context
AppContext::clear(): self

// Add provider programmatically
AppContext::addProvider(ContextProvider $provider): self

// Add channel programmatically
AppContext::addChannel(ContextChannel $channel): self
```

### When to Use Each Method

#### `all()` and `get()`
Use for normal context access. These methods are optimized with smart caching.

#### `has()`
Use to check if a context key exists before accessing it:
```php
if (AppContext::has('user.email')) {
    $email = AppContext::get('user.email');
}
```

#### `refresh()`
Rarely needed thanks to smart caching. Use only when:
- You've manually modified application state outside normal flow
- You need to force complete recalculation for testing

```php
// Example: After changing tenant in multi-tenancy
Tenant::switch($newTenant);
AppContext::refresh();
```

#### `clearProviderCache()`
For granular cache control when you know only specific provider needs refresh:
```php
use JuniorFontenele\LaravelAppContext\Providers\TimestampProvider;

// Only recalculate timestamp on next access
AppContext::clearProviderCache(TimestampProvider::class);
```

## Built-in Providers

### TimestampProvider
Adds the current timestamp to the context.
- **Cacheable**: ❌ No (timestamp can change during request lifecycle)

### AppProvider
Collects application information (name, environment, debug mode, URL, timezone, locale, origin).
- **Cacheable**: ✅ Yes (application configuration is static)

### HostProvider
Collects host information (hostname and IP address).
- **Cacheable**: ✅ Yes (host information doesn't change)

### RequestProvider
Collects HTTP request information (only runs for web requests).
- **Cacheable**: ❌ No (request data can be modified by middlewares)

### UserProvider
Collects authenticated user information (only runs when a user is authenticated).
- **Cacheable**: ❌ No (authentication state can change during request)

## Built-in Channels

### LogChannel
Registers context in Laravel's Context system (available since Laravel 11), making it automatically available in all application logs. The context is added using `Context::add()` and will be included in every log entry.

## Environment Variables

Control the package behavior with these environment variables:

```env
# Enable/disable the package
LARAVEL_APP_CONTEXT_ENABLED=true
```

## Use Cases

- **Enhanced Logging**: Automatically add rich context to all your logs through Laravel's Context system
- **Debugging**: Track request flow with complete context information available in every log entry
- **Error Tracking**: Register context in Sentry or similar services to get detailed error reports with always-fresh user data
- **Performance Monitoring**: Add context to APM tools for better performance insights
- **Auditing**: Track user actions with complete environmental context, automatically updated on authentication changes
- **Multi-tenancy**: Context automatically stays in sync as users switch between tenants

## Testing

```bash
composer test
```

## Credits

- [Junior Fontenele](https://github.com/juniorfontenele)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
