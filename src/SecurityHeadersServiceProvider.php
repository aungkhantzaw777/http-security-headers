<?php

namespace AungKhant\HttpSecurityHeaders;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;

class SecurityHeadersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/security-headers.php', 'security-headers');

        $this->app->singleton(SecurityHeadersManager::class, fn ($app) => new SecurityHeadersManager(
            $app->make(Repository::class),
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/security-headers.php' => $this->app->configPath('security-headers.php'),
            ], 'security-headers-config');

            $this->registerCommands();
        }
    }

    protected function registerCommands(): void
    {
        // Register future package commands here using $this->commands([...]).
    }
}
