<?php
declare(strict_types=1);

namespace Tenanted\Core\Database\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;

/**
 *
 */
class HasManyThroughHandler extends BaseRelationHandler
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

            if ($loaded instanceof Collection) {
                if (! $loaded->pluck($tenant->getTenantKeyName())->contains($tenant->getTenantKey())) {
                    throw new RuntimeException('Model tenant is not current tenant');
                }
            } else if (($loaded instanceof $tenant::class) && $loaded->getTenantKey() !== $tenant->getTenantKey()) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        }

        // We can't check do any more checks, because the through relationships
        // do not make it easy to work with
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