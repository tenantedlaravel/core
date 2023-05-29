<?php
declare(strict_types=1);

namespace Tenanted\Core\Database\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tenanted\Core\Contracts\Tenancy;

/**
 * No Relation Handler
 *
 * Handles models that relate to a tenants through an attribute value, rather
 * than a proper Eloquent relation.
 */
final class NoRelationHandler extends BaseRelationHandler
{
    /**
     * Get the name of the attribute that contains the tenant key
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    private function getAttributeName(Model $model): string
    {
        /** @var \Tenanted\Core\Concerns\OwnedByTenant $model */
        return $model->getTenantRelatedKeyName();
    }

    /**
     * Populate the related attribute with the tenant key
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    protected function populate(Model $model, Tenancy $tenancy): void
    {
        $model->setAttribute($this->getAttributeName($model), $tenancy->key());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    public function populateForCreation(Model $model, Tenancy $tenancy): void
    {
        $this->populate($model, $tenancy);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    public function populateAfterLoading(Model $model, Tenancy $tenancy): void
    {
        $this->populate($model, $tenancy);
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

        return $builder->where($this->getAttributeName($model), '=', $tenant->getTenantKey());
    }
}