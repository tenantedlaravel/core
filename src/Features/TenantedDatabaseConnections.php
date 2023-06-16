<?php
declare(strict_types=1);

namespace Tenanted\Core\Features;

use Closure;
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
        /**
         * @psalm-suppress InaccessibleProperty
         * @psalm-suppress PossiblyNullArgument
         * @psalm-suppress MixedArrayOffset
         */
        DatabaseManager::macro('driver', Closure::bind(function (array $config, string $name) {
            /**
             * @var array{driver:string|null}            $config
             * @var \Illuminate\Database\DatabaseManager $this
             */

            $driver = $config['driver'];

            if ($driver !== null && isset($this->extensions[$driver])) {
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