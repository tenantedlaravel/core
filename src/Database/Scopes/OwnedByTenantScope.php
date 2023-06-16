<?php

namespace Tenanted\Core\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use RuntimeException;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\TenantedManager;

/**
 */
class OwnedByTenantScope implements Scope
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
     * @return \Tenanted\Core\Contracts\Tenancy
     */
    protected function getCurrentTenancy(): Tenancy
    {
        $tenancy = $this->manager->current();

        if ($tenancy === null) {
            throw new RuntimeException('No tenancy');
        }

        return $tenancy;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $builder
     * @param \Illuminate\Database\Eloquent\Model                                        $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // @phpstan-ignore-next-line
        /**
         * @psalm-suppress UndefinedDocblockClass
         * @var \Illuminate\Database\Eloquent\Model&\Tenanted\Core\Concerns\OwnedByTenant $model
         * @var \Tenanted\Core\Contracts\TenantRelationHandler|null                       $handler
         */
        $handler = $model->getTenantRelationHandler();

        if ($handler === null) {
            throw new RuntimeException('Tenant owned model not booted properly');
        }

        $handler->scopeForQuery($model, $this->getCurrentTenancy(), $builder);
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $builder
     *
     * @return void
     */
    public function extend(Builder $builder): void
    {
        // Macro for querying against all entries
        $builder->macro('withoutTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
