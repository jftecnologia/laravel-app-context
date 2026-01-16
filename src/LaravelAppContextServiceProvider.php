<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext;

use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelAppContext\Services\ContextManager;

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
            $this->app->make(ContextManager::class)->build();
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

        $this->app->singleton(ContextManager::class, function ($app) {
            $config = config('app-context');

            $manager = new ContextManager($config);

            foreach ($config['providers'] as $providerClass) {
                $manager->addProvider($app->make($providerClass));
            }

            foreach ($config['channels'] as $channelName => $channelClass) {
                $manager->addChannel($app->make($channelClass, ['config' => $config['channel_settings ']]));
            }

            return $manager;
        });

        $this->app->alias(ContextManager::class, 'app-context');
    }
}
