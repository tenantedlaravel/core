<?php
declare(strict_types=1);

namespace Tenanted\Core\Database\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;

/**
 * Belongs To Many Relation Handler
 *
 * Handles models that relate to multiple tenants through a belongs to relationship
 */
class HasManyHandler extends BaseRelationHandler
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

            // If there are already a few tenants, and none are the current one,
            // there's an issue
            if (! $loaded->pluck($tenant->getTenantKeyName())->contains($tenant->getTenantKey())) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        }

        // We can't check do any more checks, really, without adding an extra
        // query, which we really don't want
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

        $model->{$relationName}()->attach($tenant->getTenantKey());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    public function populateAfterLoading(Model $model, Tenancy $tenancy): void
    {
        /**
         * @var \Tenanted\Core\Contracts\Tenant|\Illuminate\Database\Eloquent\Model|null $tenant
         */
        $tenant = $tenancy->tenant();

        if ($tenant === null) {
            return;
        }

        $this->checkCurrentValue($model, $tenant, $tenancy);

        $relationName = $this->getRelationName($model, $tenancy);

        if ($model->relationLoaded($relationName)) {
            if (! $model->getRelation($relationName)->pluck($tenant->getTenantKeyName())->contains($tenant->getTenantKey())) {
                throw new RuntimeException('Returned model does not belong to the current tenant');
            }
        } else {
            $model->setRelation($relationName, $tenant->newCollection([$tenant]));
        }
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

        $relationName = $this->getRelationName($model, $tenancy);
        /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation */
        $relation = $model->{$relationName}();

        return $builder->whereHas($relationName, function (Builder $builder) use ($tenant, $relation) {
            $builder->where($relation->getQualifiedForeignPivotKeyName(), '=', $tenant->getTenantKey());
        });
    }
}