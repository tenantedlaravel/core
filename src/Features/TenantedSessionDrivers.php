<?php
declare(strict_types=1);

namespace Tenanted\Core\Features;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Session\EncryptedStore;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;
use SessionHandlerInterface;
use Tenanted\Core\Services\Session\TenantedDatabaseSessionHandler;
use Tenanted\Core\TenantedManager;

/**
 * @psalm-suppress InvalidReturnType
 */
class TenantedSessionDrivers extends BaseFeature
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    public function initialise(): void
    {
        // Intentionally empty
    }

    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->app->make(SessionManager::class)
                  ->extend('tenanted:file', $this->createFileDriver(...))
                  ->extend('tenanted:cookie', $this->createCookieDriver(...))
                  ->extend('tenanted:database', $this->createDatabaseDriver(...))
                  ->extend('tenanted:apc', $this->createApcDriver(...))
                  ->extend('tenanted:memcached', $this->createMemcachedDriver(...))
                  ->extend('tenanted:redis', $this->createRedisDriver(...))
                  ->extend('tenanted:dynamodb', $this->createDynamodbDriver(...))
                  ->extend('tenanted:array', $this->createArrayDriver(...));
    }

    protected function createFileDriver(Container $container): EncryptedStore|Store
    {

    }

    protected function createCookieDriver(Container $container): EncryptedStore|Store
    {

    }

    protected function createDatabaseDriver(Container $container): EncryptedStore|Store
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config  = $container->make('config');
        $handler = new TenantedDatabaseSessionHandler(
            $container->make(TenantedManager::class),
            $container->make('db')->connection($config->get('session.connection')),
            (string) $config->get('session.table'),
            (int) $config->get('session.lifetime'),
            $container
        );

        return $config->get('session.encrypt')
            ? $this->buildEncryptedSession($handler, $config, $container)
            : $this->buildSession($handler, $config);
    }

    protected function createApcDriver(Container $container): EncryptedStore|Store
    {

    }

    protected function createMemcachedDriver(Container $container): EncryptedStore|Store
    {

    }

    protected function createRedisDriver(Container $container): EncryptedStore|Store
    {

    }

    protected function createDynamodbDriver(Container $container): EncryptedStore|Store
    {

    }

    protected function createArrayDriver(Container $container): EncryptedStore|Store
    {

    }

    /**
     * Build the session instance.
     *
     * @param \SessionHandlerInterface                $handler
     * @param \Illuminate\Contracts\Config\Repository $config
     *
     * @return \Illuminate\Session\Store
     */
    protected function buildSession(SessionHandlerInterface $handler, Repository $config): Store
    {
        return new Store(
            (string) $config->get('session.cookie'),
            $handler,
            null,
            (string) $config->get('session.serialization', 'php')
        );
    }

    /**
     * Build the encrypted session instance.
     *
     * @param \SessionHandlerInterface                  $handler
     * @param \Illuminate\Contracts\Config\Repository   $config
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return \Illuminate\Session\EncryptedStore
     */
    protected function buildEncryptedSession(SessionHandlerInterface $handler, Repository $config, Container $container): EncryptedStore
    {
        return new EncryptedStore(
            (string) $config->get('session.cookie'),
            $handler,
            $container['encrypter'],
            null,
            (string) $config->get('session.serialization', 'php'),
        );
    }
}