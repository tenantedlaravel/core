<?php
declare(strict_types=1);

namespace Tenanted\Core\Database\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;

/**
 * Belongs To Relation Handler
 *
 * Handles models that relate to a tenant through a belongs to relationship
 */
class BelongsToHandler extends BaseRelationHandler
{
    /**
     * Check if the models current tenant matches the current tenant
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenant     $tenant
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    private function checkCurrentValue(Model $model, Tenant $tenant, Tenancy $tenancy): void
    {
        $relationName = $this->getRelationName($model, $tenancy);

        if ($model->relationLoaded($relationName)) {
            $loaded = $model->getRelation($relationName);

            // If there's already a tenant, and it isn't the current one, there's
            // an issue
            if ($loaded->getTenantKey() !== $tenant->getTenantKey()) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        } else {
            // Since this is a 'belongs to' relation, we can check the foreign
            // key without generating a query
            $relation = $model->{$relationName}();
            $key      = $model->getAttribute($relation->getForeignKeyName());

            if ($key !== $tenant->getTenantKey()) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    public function populateForCreation(Model $model, Tenancy $tenancy): void
    {
        $tenant = $tenancy->tenant();

        if ($tenant === null) {
            return;
        }

        $this->checkCurrentValue($model, $tenant, $tenancy);

        $relationName = $this->getRelationName($model, $tenancy);

        $model->{$relationName}()->associate($tenant);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    public function populateAfterLoading(Model $model, Tenancy $tenancy): void
    {
        $tenant = $tenancy->tenant();

        if ($tenant === null) {
            return;
        }

        $this->checkCurrentValue($model, $tenant, $tenancy);

        $relationName = $this->getRelationName($model, $tenancy);

        $model->setRelation($relationName, $tenant);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model   $model
     * @param \Tenanted\Core\Contracts\Tenancy      $tenancy
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForQuery(Model $model, Tenancy $tenancy, Builder $builder): Builder
    {
        /**
         * @var \Tenanted\Core\Contracts\Tenant|\Illuminate\Database\Eloquent\Model|null $tenant
         */
        $tenant = $tenancy->tenant();

        if ($tenant === null) {
            return $builder;
        }

        if (method_exists($model, 'getTenantRelationName')) {
            $relationName = $model->getTenantRelationName();
        } else {
            $relationName = $tenancy->name();
        }

        $relation = $model->{$relationName}();

        return $builder->where($relation->getForeignKeyName(), '=', $tenant->getTenantKey());
    }
}