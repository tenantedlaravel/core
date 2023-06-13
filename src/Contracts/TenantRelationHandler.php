<?php

namespace Tenanted\Core\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Tenant Relation Handler
 *
 * Tenant relation handlers are classes that encapsulate the logic for handling
 * a models' relation to a tenant. They are responsible for populating the
 * relation or attribute during creation, populating with the current tenant
 * after querying, and scoping queries to the current tenant.
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 */
interface TenantRelationHandler
{
    /**
     * Populate the tenant relation when the model is created
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    public function populateForCreation(Model $model, Tenancy $tenancy): void;

    /**
     * Populate the tenant relation after model hydration
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    public function populateAfterLoading(Model $model, Tenancy $tenancy): void;

    /**
     * Scope a query to the current tenant
     *
     * @param \Illuminate\Database\Eloquent\Model   $model
     * @param \Tenanted\Core\Contracts\Tenancy      $tenancy
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopeForQuery(Model $model, Tenancy $tenancy, Builder $builder): Builder;
}