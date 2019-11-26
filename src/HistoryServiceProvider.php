<?php

namespace Bigin\History;

use Illuminate\Support\ServiceProvider;

class HistoryServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/history.php', 'bigin.history');

        // Register the service the package provides.
        $this->app->singleton('history', function ($app) {
            return new History;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['history'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/history.php' => config_path('bigin/history.php'),
        ], 'bigin.history.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/bigin'),
        ], 'history.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/bigin'),
        ], 'history.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/bigin'),
        ], 'history.views');*/

        // Publishing the migration files.
        $migrations = realpath(__DIR__.'/../database/migrations');

        $this->publishes([
            $migrations => $this->app->databasePath().'/migrations',
        ], 'bigin.history.migrations');

        // Registering package commands.
        // $this->commands([]);
    }
}
