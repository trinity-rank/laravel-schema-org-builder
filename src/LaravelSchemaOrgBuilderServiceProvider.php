<?php

namespace Trinityrank\LaravelSchemaOrgBuilder;

use Illuminate\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;

class LaravelSchemaOrgBuilderServiceProvider extends ServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-schema-org-builder');
    }
    
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ ."/../config/schema-org-builder.php" =>
                config_path('schema-org-builder.php'),
            ], "schema-org-builder");
        }
    }
}
