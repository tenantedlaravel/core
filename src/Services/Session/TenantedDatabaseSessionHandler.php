<?php
declare(strict_types=1);

namespace Tenanted\Core\Services\Session;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Arr;
use Tenanted\Core\Support\TenantedHelper;
use Tenanted\Core\TenantedManager;

/**
 *
 */
class TenantedDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * @var \Tenanted\Core\TenantedManager
     */
    private TenantedManager $manager;

    /**
     * @param \Tenanted\Core\TenantedManager                 $manager
     * @param \Illuminate\Database\ConnectionInterface       $connection
     * @param                                                $table
     * @param                                                $minutes
     * @param \Illuminate\Contracts\Container\Container|null $container
     */
    public function __construct(TenantedManager $manager, ConnectionInterface $connection, $table, $minutes, Container $container = null)
    {
        parent::__construct($connection, $table, $minutes, $container);

        $this->manager = $manager;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery(): Builder
    {
        $tenancy = $this->manager->current();

        // If there isn't an active tenancy or tenant, we can just return the
        // base query
        if (! $tenancy || ! $tenancy->check()) {
            return parent::getQuery();
        }

        // There's a tenancy and a tenant, so we scope the query
        return parent::getQuery()
                     ->where(
                         TenantedHelper::tenantRelatedKeyName($tenancy),
                         '=',
                         $tenancy->key()
                     );
    }

    /**
     * @param $sessionId
     * @param $payload
     *
     * @return bool|void|null
     */
    protected function performInsert($sessionId, $payload)
    {
        try {
            Arr::set($payload, 'id', $sessionId);

            $tenancy = $this->manager->current();

            // If there's a tenancy and tenant, we'll set the tenant key on the
            // query
            if ($tenancy && $tenancy->check()) {
                Arr::set($payload, TenantedHelper::tenantRelatedKeyName($tenancy), $tenancy->key());
            }

            return parent::getQuery()->insert($payload);
        } catch (QueryException) {
            $this->performUpdate($sessionId, $payload);
        }
    }
}