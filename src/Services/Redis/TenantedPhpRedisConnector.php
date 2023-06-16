<?php
declare(strict_types=1);

namespace Tenanted\Core\Services\Redis;

use Illuminate\Redis\Connections\PhpRedisClusterConnection;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Redis\Connectors\PhpRedisConnector;
use RuntimeException;
use Tenanted\Core\TenantedManager;

/**
 *
 */
class TenantedPhpRedisConnector extends PhpRedisConnector
{
    /**
     * @var \Tenanted\Core\TenantedManager
     */
    private TenantedManager $manager;

    /**
     * @param \Tenanted\Core\TenantedManager $manager
     */
    public function __construct(TenantedManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param array<array-key, mixed> $config
     * @param array<array-key, mixed> $options
     *
     * @return \Illuminate\Redis\Connections\PhpRedisConnection
     */
    public function connect(array $config, array $options): PhpRedisConnection
    {
        $this->prefixByTenant($config, $options);

        return parent::connect($config, $options);
    }

    /**
     * @param array<array-key, mixed> $config
     * @param array<array-key, mixed> $clusterOptions
     * @param array<array-key, mixed> $options
     *
     * @return \Illuminate\Redis\Connections\PhpRedisClusterConnection
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options): PhpRedisClusterConnection
    {
        $this->prefixByTenant($config, $clusterOptions, $options);

        return parent::connectToCluster($config, $clusterOptions, $options);
    }

    /**
     * @param array<array-key, mixed> $config
     * @param array<array-key, mixed> $options
     * @param array<array-key, mixed> $options2
     *
     * @return void
     */
    private function prefixByTenant(array &$config, array &$options, array &$options2 = []): void
    {
        $tenant = $this->manager->current()?->tenant();

        if ($tenant === null) {
            throw new RuntimeException('No current tenant');
        }

        /**
         * @var string|null $prefix
         * @psalm-suppress MixedArrayAccess
         * @phpstan-ignore-next-line
         */
        $prefix = $options2['prefix'] ?? $options['prefix'] ?? $config['options']['prefix'] ?? null;
        /**
         * @psalm-suppress MixedArrayAccess
         * @phpstan-ignore-next-line
         */
        unset($options2['prefix'], $options['prefix'], $config['options']['prefix']);
        /**
         * @psalm-suppress MixedArrayAssignment
         * @phpstan-ignore-next-line
         */
        $config['options']['prefix'] = $tenant->getTenantIdentifier() . ($prefix ? ':' . $prefix : '');
    }
}