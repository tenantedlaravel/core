<?php
declare(strict_types=1);

namespace Tenanted\Core;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Support\TenantedManager;

class TenantedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBindings();
    }

    private function registerBindings(): void
    {
        // The TenantedManager only requires an instance of Application, so it
        // can be marked as a singleton without a concrete resolution.
        $this->app->singleton(TenantedManager::class);

        // When requesting a TenantProvider, we want to return the default
        // implementation; unless, it was requested with parameters that contain
        // a 'name'.
        $this->app->bind(TenantProvider::class, function (Application $app, array $parameters) {
            return $app->make(TenantedManager::class)->provider($parameters['name'] ?? null);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->bootConsole();
        }
    }

    private function bootConsole(): void
    {
        $this->publishes(
            [
                $this->packageDir('config', 'tenanted.php') => config_path('tenanted.php'),
            ],
            'config'
        );
    }

    /**
     * @param string ...$paths
     *
     * @return string
     */
    private function packageDir(string... $paths): string
    {
        return basename(__DIR__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $paths);
    }
}