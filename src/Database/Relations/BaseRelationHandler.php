<?php
declare(strict_types=1);

namespace Tenanted\Core\Database\Relations;

use Illuminate\Database\Eloquent\Model;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\TenantRelationHandler;

abstract class BaseRelationHandler implements TenantRelationHandler
{
    /**
     * Get the name of the relation
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return string
     */
    protected function getRelationName(Model $model, Tenancy $tenancy): string
    {
        if (method_exists($model, 'getTenantRelationName')) {
            return $model->getTenantRelationName();
        }

        return $tenancy->name();
    }
}