<?php
declare(strict_types=1);

namespace Tenanted\Core\Providers;

use Illuminate\Database\Eloquent\Model;
use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Contracts\TenantProvider;

/**
 * Eloquent Tenant Provider
 *
 * @template M of \Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant
 */
class EloquentTenantProvider implements TenantProvider
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var class-string<M>
     */
    private string $model;

    /**
     * @var M
     * @noinspection PhpDocFieldTypeMismatchInspection
     */
    private Model&Tenant $instance;

    /**
     * @param string          $name
     * @param class-string<M> $model
     */
    public function __construct(string $name, string $model)
    {
        $this->name  = $name;
        $this->model = $model;
    }

    /**
     * Get an instance of the model this provider uses
     *
     * @return M
     */
    public function getModel(): Model&Tenant
    {
        if (! isset($this->instance)) {
            $this->instance = new $this->model;
        }

        return new $this->instance;
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
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->model;
    }

    /**
     * @param string $identifier
     *
     * @return M|null
     *
     * @api
     */
    public function retrieveByIdentifier(string $identifier): ?Tenant
    {
        return $this->retrieveBy(
            $this->getModel()->getTenantIdentifierName(),
            $identifier
        );
    }

    /**
     * @param mixed $key
     *
     * @return M|null
     *
     * @api
     */
    public function retrieveByKey(mixed $key): ?Tenant
    {
        return $this->retrieveBy(
            $this->getModel()->getTenantKeyName(),
            $key
        );
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return M|null
     *
     * @api
     */
    public function retrieveBy(string $name, mixed $value): ?Tenant
    {
        $model = $this->getModel();

        return $model->newQuery()
                     ->where($name, '=', $value)
                     ->first();
    }
}