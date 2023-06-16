<?php
declare(strict_types=1);

namespace Tenanted\Core\Services\Redis;

use Illuminate\Redis\Connections\PredisClusterConnection;
use Illuminate\Redis\Connections\PredisConnection;
use Illuminate\Redis\Connectors\PredisConnector;
use RuntimeException;
use Tenanted\Core\TenantedManager;

/**
 *
 */
class TenantedPredisConnector extends PredisConnector
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
     * @return \Illuminate\Redis\Connections\PredisConnection
     */
    public function connect(array $config, array $options): PredisConnection
    {
        $this->prefixByTenant($config, $options);

        return parent::connect($config, $options);
    }

    /**
     * @param array<array-key, mixed> $config
     * @param array<array-key, mixed> $clusterOptions
     * @param array<array-key, mixed> $options
     *
     * @return \Illuminate\Redis\Connections\PredisClusterConnection
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options): PredisClusterConnection
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

        /** @var string|null $prefix */
        $prefix = $options2['prefix'] ?? $options['prefix'] ?? $config['prefix'] ?? null;
        unset($options2['prefix'], $options['prefix'], $config['prefix']);

        $config['prefix'] = $tenant->getTenantIdentifier() . ($prefix ? ':' . $prefix : '');
    }
}