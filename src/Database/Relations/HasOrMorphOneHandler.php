<?php
declare(strict_types=1);

namespace Tenanted\Core\Database\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use RuntimeException;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;

/**
 * Has One Relation Handler
 *
 * Handles models that relate to a tenant through a has one relationship
 */
class HasOrMorphOneHandler extends BaseRelationHandler
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
        /** @var \Tenanted\Core\Contracts\Tenant&\Illuminate\Database\Eloquent\Model $tenant */

        $relationName = $this->getRelationName($model, $tenancy);

        if ($model->relationLoaded($relationName)) {
            /** @var \Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant $loaded */
            $loaded = $model->getRelation($relationName);

            // If there's already a tenant, and it isn't the current one, there's
            // an issue
            if ($loaded->getTenantKey() !== $tenant->getTenantKey()) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        } else {
            /** @var \Illuminate\Database\Eloquent\Relations\HasOne<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Relations\MorphOne<\Illuminate\Database\Eloquent\Model> $relation */
            $relation   = $model->{$relationName}();
            $foreignKey = $tenant->getAttribute($relation->getForeignKeyName());
            $localKey   = $model->getKey();

            if ($foreignKey !== $localKey) {
                throw new RuntimeException('Model tenant is not current tenant');
            }

            // If the morph is for a different class, there's an issue
            if ($relation instanceof MorphOne && $relation->getMorphClass() !== $tenant::class) {
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
        /**
         * @var (\Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant)|null $tenant
         */
        $tenant = $tenancy->tenant();

        if ($tenant === null) {
            return;
        }

        $this->checkCurrentValue($model, $tenant, $tenancy);

        $relationName = $this->getRelationName($model, $tenancy);

        /** @var \Illuminate\Database\Eloquent\Relations\HasOne<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Relations\MorphOne<\Illuminate\Database\Eloquent\Model> $relation */
        $relation = $model->{$relationName}();
        $relation->save($tenant);

        $model->setRelation($relationName, $tenant);
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
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopeForQuery(Model $model, Tenancy $tenancy, Builder $builder): Builder
    {
        /**
         * @var (\Tenanted\Core\Contracts\Tenant&\Illuminate\Database\Eloquent\Model)|null $tenant
         */
        $tenant = $tenancy->tenant();

        if ($tenant === null) {
            return $builder;
        }

        $relationName = $this->getRelationName($model, $tenancy);
        /** @var \Illuminate\Database\Eloquent\Relations\HasOne<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Relations\MorphOne<\Illuminate\Database\Eloquent\Model> $relation */
        $relation = $model->{$relationName}();

        return $builder->whereHas($relationName, function (Builder $builder) use ($tenant, $relation) {
            $builder->where($relation->getQualifiedForeignKeyName(), '=', $tenant->getTenantKey());
        });
    }
}