<?php
declare(strict_types=1);

namespace Tenanted\Core\Features;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;

class TenantedDatabaseConnections extends BaseFeature
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function initialise(): void
    {
        // Alias the binding for the database manager; I honestly don't know why
        // this hasn't been done already.
        $this->app->alias('db', DatabaseManager::class);

        // Create a macro to create new driver instances.
        DatabaseManager::macro('driver', Closure::bind(function (array $config, string $name) {
            /**
             * @var \Illuminate\Database\DatabaseManager $this
             */
            if (isset($this->extensions[$driver = $config['driver']])) {
                return call_user_func($this->extensions[$driver], $config, $name);
            }

            return $this->factory->make($config, $name);
        }, $this->app->make(DatabaseManager::class), DatabaseManager::class));
    }

    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    public function boot(): void
    {
        // Intentionally empty
    }
}