<?php
declare(strict_types=1);

namespace Tenanted\Core\Features;

use Illuminate\Redis\RedisManager;
use Tenanted\Core\Services\Redis\TenantedPhpRedisConnector;
use Tenanted\Core\Services\Redis\TenantedPredisConnector;
use Tenanted\Core\TenantedManager;

class TenantedRedisDrivers extends BaseFeature
{
    public function initialise(): void
    {
        // Intentionally empty
    }

    public function boot(): void
    {
        $this->app->make(RedisManager::class)
                  ->extend('tenanted:predis', $this->createPredisDriver(...))
                  ->extend('tenanted:phpredis', $this->createPhpRedisDriver(...));
    }

    private function createPredisDriver(): TenantedPredisConnector
    {
        return new TenantedPredisConnector($this->app->make(TenantedManager::class));
    }

    private function createPhpRedisDriver(): TenantedPhpRedisConnector
    {
        return new TenantedPhpRedisConnector($this->app->make(TenantedManager::class));
    }
}