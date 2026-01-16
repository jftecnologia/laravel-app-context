<?php

declare(strict_types = 1);

namespace VendorName\PackageName;

use Illuminate\Support\ServiceProvider;

class PackageNameServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/package_slug.php' => config_path('package_slug.php'),
        ], 'package_slug-config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/package_slug.php', 'package_slug');
    }
}
