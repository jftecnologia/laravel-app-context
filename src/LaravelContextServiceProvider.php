<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelContext;

use Illuminate\Support\ServiceProvider;

class LaravelContextServiceProvider extends ServiceProvider
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
                __DIR__ . '/../config/laravel-context.php' => config_path('laravel-context.php'),
            ], 'laravel-context-config');
        }

        if (config('laravel-context.enabled', true)) {
            $contextManager = $this->app->make(ContextManager::class);

            $contextManager->build();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-context.php', 'laravel-context');

        $this->app->singleton(ContextManager::class, function ($app): ContextManager {
            $config = $app['config']->get('laravel-context');

            $contextManager = new ContextManager();

            foreach ($config['providers'] as $providerClass) {
                $contextManager->addProvider($app->make($providerClass));
            }

            foreach ($config['channels'] as $channelClass) {
                $contextManager->addChannel($app->make($channelClass));
            }

            return $contextManager;
        });

        $this->app->alias(ContextManager::class, 'laravel-context');
    }
}
