<?php
declare(strict_types=1);

namespace Tenanted\Core;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Http\Middleware\TenantedRoutes;

class TenantedServiceProvider extends ServiceProvider
{
    /**
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->registerBindings();
        $this->registerMiddleware();
        $this->registerMacros();
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

    /**
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerMiddleware(): void
    {
        // Alias the middleware with the router.
        $this->app->make(Router::class)->aliasMiddleware('tenanted', TenantedRoutes::class);

        // Make sure the tenanted middleware has the highest priority according
        // to the order they're defined in the config.
        $kernel = $this->app->make(Kernel::class);

        // There shouldn't ever be a reason for the returned value to not be an
        // instance of this, but, I can't bare the IDE errors because the method
        // doesn't exist on the default Kernel contract.
        if ($kernel instanceof \Illuminate\Foundation\Http\Kernel) {
            $kernel->prependToMiddlewarePriority(TenantedRoutes::class);
        }
    }

    private function registerMacros(): void
    {
        // Register the router macro to easily register tenant routes
        $this->app->make(Router::class)::macro(
            'tenanted',
            function (?string $resolver = null, ?string $tenancy = null, ?string $value = null) {
                return $this->app->make(TenantedManager::class)->routes($resolver, $tenancy, $value);
            }
        );
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
    private function packageDir(string...$paths): string
    {
        return basename(__DIR__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $paths);
    }
}