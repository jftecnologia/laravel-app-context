<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext;

use Illuminate\Support\ServiceProvider;

class LaravelAppContextServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/app-context.php' => config_path('app-context.php'),
            ], 'laravel-app-context-config');
        }

        if (config('app-context.enabled', true)) {
            $contextManager = $this->app->make(ContextManager::class);

            $contextManager->resolveContext();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/app-context.php', 'app-context');

        $this->app->singleton(ContextManager::class, function ($app): ContextManager {
            $config = config('app-context');

            $contextManager = new ContextManager($config);

            if ($config['enabled'] ?? true) {
                foreach ($config['providers'] as $providerClass) {
                    $contextManager->addProvider($this->app->make($providerClass));
                }

                foreach ($config['channels'] as $channelClass) {
                    $contextManager->addChannel($this->app->make($channelClass));
                }
            }

            return $contextManager;
        });

        $this->app->alias(ContextManager::class, 'app-context');
    }
}
