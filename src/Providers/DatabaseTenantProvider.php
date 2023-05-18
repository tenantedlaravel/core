<?php
declare(strict_types=1);

namespace Tenanted\Core\Providers;

use Illuminate\Database\ConnectionInterface;
use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Support\TenantEntity;

/**
 * Database Tenant Provider
 */
class DatabaseTenantProvider implements TenantProvider
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private ConnectionInterface $connection;

    /**
     * @var string
     */
    private string $table;

    /**
     * @var string
     */
    private string $identifier;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var class-string<\Tenanted\Core\Contracts\Tenant>
     */
    private string $entity;

    /**
     * @param string                                        $name
     * @param \Illuminate\Database\ConnectionInterface      $connection
     * @param string                                        $table
     * @param string                                        $identifier
     * @param string                                        $key
     * @param class-string<\Tenanted\Core\Contracts\Tenant> $entity
     */
    public function __construct(
        string              $name,
        ConnectionInterface $connection,
        string              $table,
        string              $identifier = 'identifier',
        string              $key = 'id',
        string              $entity = TenantEntity::class
    )
    {
        $this->name       = $name;
        $this->connection = $connection;
        $this->table      = $table;
        $this->identifier = $identifier;
        $this->key        = $key;
        $this->entity     = $entity;
    }

    /**
     * @return string
     *
     * @api
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $identifier
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     *
     * @api
     */
    public function retrieveByIdentifier(string $identifier): ?Tenant
    {
        return $this->retrieveBy($this->identifier, $identifier);
    }

    /**
     * @param mixed $key
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     *
     * @api
     */
    public function retrieveByKey(mixed $key): ?Tenant
    {
        return $this->retrieveBy($this->key, $key);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     *
     * @api
     */
    public function retrieveBy(string $name, mixed $value): ?Tenant
    {
        return $this->makeEntity(
            $this->connection->table($this->table)->where($name, '=', $value)->first()
        );
    }

    /**
     * @param object|null $data
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    private function makeEntity(?object $data): ?Tenant
    {
        if ($data === null) {
            return null;
        }

        $entity = $this->entity;

        return new $entity(
            $this->identifier,
            $this->key,
            (array) $data
        );
    }
}