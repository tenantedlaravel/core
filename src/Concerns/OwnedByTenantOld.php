<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use RuntimeException;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Contracts\TenantRelationHandler;
use Tenanted\Core\Database\Relations\NoRelationHandler;
use Tenanted\Core\Support\TenantedHelper;
use Tenanted\Core\TenantedManager;

/**
 * Owner by Tenant
 *
 * This trait is for models that are considered owned by a tenant. It will
 * automatically scope queries and populate relations based on the current
 * tenant.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait OwnedByTenantOld
{
    protected static TenantRelationHandler $tenantRelationHandler;

    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootOwnedByTenant(): void
    {
        // Set the creating callback
        static::creating(function (Model $model) {
            $this->populateTenantForCreation($model);
        });

        // Set the retrieved callback
        static::retrieved(function (Model $model) {
            $this->populateTenantRelation($model);
        });
    }

    /**
     * @var bool
     */
    protected bool $populateTenant = true;

    /**
     * @return string|null
     */
    public function getTenantRelatedKeyName(): ?string
    {
        return TenantedHelper::tenantRelatedKeyName($this->getCurrentTenancy());
    }

    /**
     * @return string|null
     */
    public function getTenantRelationName(): ?string
    {
        return $this->getCurrentTenancy()->name();
    }

    public function getTenantRelationHandler()
    {
        if (! isset(self::$tenantRelationHandler)) {
            if (! $this->isRelation($this->getTenantRelationName())) {
                self::$tenantRelationHandler = new NoRelationHandler();
            } else {
                $relation = $this->{$this->getTenantRelationName()}();

                if ($relation instanceof BelongsTo) {
                    $this->saveBelongsToTenant($this, $this->getCurrentTenancy());
                } else if ($relation instanceof BelongsToMany) {
                    $this->saveBelongsToManyTenant($this, $this->getCurrentTenancy());
                } else if ($relation instanceof HasOne || $relation instanceof MorphOne) {
                    $this->saveHasOneTenant($this, $this->getCurrentTenancy());
                } else if ($relation instanceof HasOneOrMany) {
                    $this->saveHasManyTenant($this, $this->getCurrentTenancy());
                }
            }
        }

        return self::$tenantRelationHandler;
    }

    /**
     * @return \Tenanted\Core\Contracts\Tenancy
     */
    private function getCurrentTenancy(): Tenancy
    {
        $tenancy = app(TenantedManager::class)->current();

        if ($tenancy === null) {
            throw new RuntimeException('No tenancy');
        }

        return $tenancy;
    }

    /**
     * Set the tenant relation
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    private function populateTenantForCreation(Model $model): void
    {
        $tenancy = $this->getCurrentTenancy();

        // If there's no tenant, we error because this is a tenant owned model,
        // and we need a tenant for that
        if (! $tenancy->check()) {
            throw new RuntimeException('No tenant');
        }

        $this->getTenantRelationHandler()->populateForCreation($model, $tenancy);

        $tenant = $tenancy->tenant();
        $name   = $tenancy->name();

        if ($tenant instanceof Model && $model->isRelation($name)) {
            // If the tenant is a model, we're dealing with a proper Eloquent
            // relation and can handle that accordingly
            /** @var \Illuminate\Database\Eloquent\Relations\Relation $relation */
            $relation = $model->{$name}();

            // We don't want to handle through relations here, the models that
            // this one relates through should have the trait
            if ($relation instanceof HasManyThrough) {
                throw new RuntimeException('Trait should only be applied to non-through related models');
            }

            $this->saveTenantRelation($model, $relation, $tenant);
        } else {
            // If the tenant isn't a model, we will use the 'getTenantRelatedKeyName'
            // method, or guess the key and set that attribute. This approach
            // will be used when the tenant has a key, but doesn't have a
            // database entry
            $attribute = $this->getTenantRelatedKeyName();
            $this->setAttribute($attribute, $tenant->getTenantKey());
        }
    }

    /**
     * Save the current tenant to the tenant relation
     *
     * @param \Illuminate\Database\Eloquent\Model                                 $model
     * @param \Illuminate\Database\Eloquent\Relations\Relation                    $relation
     * @param \Illuminate\Database\Eloquent\Model|\Tenanted\Core\Contracts\Tenant $tenant
     *
     * @return void
     */
    private function saveTenantRelation(Model $model, Relation $relation, Model|Tenant $tenant): void
    {
        if ($relation instanceof BelongsTo) {
            $this->saveBelongsToTenant($model, $relation, $tenant);
        } else if ($relation instanceof BelongsToMany) {
            $this->saveBelongsToManyTenant($model, $relation, $tenant);
        } else if ($relation instanceof HasOne || $relation instanceof MorphOne) {
            $this->saveHasOneTenant($model, $relation, $tenant);
        } else if ($relation instanceof HasOneOrMany) {
            $this->saveHasManyTenant($model, $relation, $tenant);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model                                 $model
     * @param \Illuminate\Database\Eloquent\Relations\BelongsTo                   $relation
     * @param \Illuminate\Database\Eloquent\Model|\Tenanted\Core\Contracts\Tenant $tenant
     *
     * @return void
     */
    private function saveBelongsToTenant(Model $model, BelongsTo $relation, Model|Tenant $tenant): void
    {
        // If the tenant is loaded, we'll check
        if ($model->relationLoaded($relation->getRelationName())) {
            $loaded = $model->getRelation($relation->getRelationName());

            // If there's already a tenant, and it isn't the current one, there's
            // an issue
            if ($loaded->getTenantKey() !== $tenant->getTenantKey()) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        } else {
            // Since this is a 'belongs to' relation, we can check the foreign
            // key without generating a query
            $key = $model->getAttribute($relation->getForeignKeyName());

            if ($key === null) {
                $relation->associate($tenant);
            } else if ($key !== $tenant->getTenantKey()) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model                                 $model
     * @param \Illuminate\Database\Eloquent\Relations\BelongsToMany               $relation
     * @param \Illuminate\Database\Eloquent\Model|\Tenanted\Core\Contracts\Tenant $tenant
     *
     * @return void
     */
    private function saveBelongsToManyTenant(Model $model, BelongsToMany $relation, Model|Tenant $tenant): void
    {
        $relation->attach($tenant->getTenantKey());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model                                                             $model
     * @param \Illuminate\Database\Eloquent\Relations\HasOne|\Illuminate\Database\Eloquent\Relations\MorphOne $relation
     * @param \Illuminate\Database\Eloquent\Model|\Tenanted\Core\Contracts\Tenant                             $tenant
     *
     * @return void
     */
    private function saveHasOneTenant(Model $model, HasOne|MorphOne $relation, Model|Tenant $tenant): void
    {
        // If the tenant is loaded, we'll check
        if ($model->relationLoaded($relation->getRelationName())) {
            $loaded = $model->getRelation($relation->getRelationName());

            // If there's already a tenant, and it isn't the current one, there's
            // an issue
            if ($loaded->getTenantKey() !== $tenant->getTenantKey()) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        } else {
            // If there's no loaded relation, we will just save and hope for
            // the best
            $relation->save($tenant);
            // The above doesn't actually populate the relation, so we will
            $this->populateSingleRelation($model, $relation, $tenant);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model                                 $model
     * @param \Illuminate\Database\Eloquent\Relations\HasOneOrMany                $relation
     * @param \Illuminate\Database\Eloquent\Model|\Tenanted\Core\Contracts\Tenant $tenant
     *
     * @return void
     */
    private function saveHasManyTenant(Model $model, HasOneOrMany $relation, Model|Tenant $tenant): void
    {
        $relation->save($tenant);
    }

    /**
     * Populate the tenant relation
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    private function populateTenantRelation(Model $model): void
    {
        if (! $this->populateTenant) {
            return;
        }

        $tenancy = $this->getCurrentTenancy();

        // If there's no tenant, we error because this is a tenant owned model,
        // and we need a tenant for that
        if (! $tenancy->check()) {
            throw new RuntimeException('No tenant');
        }

        $tenant = $tenancy->tenant();
        $name   = $tenancy->name();

        if ($tenant instanceof Model && $model->isRelation($name)) {
            // If the tenant is a model, we're dealing with a proper Eloquent
            // relation and can handle that accordingly
            /** @var \Illuminate\Database\Eloquent\Relations\Relation $relation */
            $relation = $model->{$name}();

            // We don't want to handle through relations here, the models that
            // this one relates through should have the trait
            if ($relation instanceof HasManyThrough) {
                throw new RuntimeException('Trait should only be applied to non-through related models');
            }

            if ($relation instanceof BelongsTo || $relation instanceof HasOne || $relation instanceof MorphOne) {
                $this->populateSingleRelation($model, $relation, $tenant);
            } else if ($relation instanceof BelongsToMany) {
                $this->populateManyRelation($model, $relation, $tenant);
            } else if ($relation instanceof HasOneOrMany) {
                $this->populateHasManyRelation($model, $relation, $tenant, $name);
            }
        } else {
            // If the tenant isn't a model, we will use the 'getTenantRelatedKeyName'
            // method, or guess the key and set that attribute. This approach
            // will be used when the tenant has a key, but doesn't have a
            // database entry
            $attribute = $this->getTenantRelatedKeyName() ?? TenantedHelper::tenantRelatedKeyName($tenancy);
            $this->setAttribute($attribute, $tenant->getTenantKey());
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model                                                                                                               $model
     * @param \Illuminate\Database\Eloquent\Relations\BelongsTo|\Illuminate\Database\Eloquent\Relations\HasOne|\Illuminate\Database\Eloquent\Relations\MorphOne $relation
     * @param \Illuminate\Database\Eloquent\Model|\Tenanted\Core\Contracts\Tenant                                                                               $tenant
     *
     * @return void
     */
    private function populateSingleRelation(Model $model, BelongsTo|HasOne|MorphOne $relation, Model|Tenant $tenant): void
    {
        // If the tenant is loaded, we'll check
        if ($model->relationLoaded($relation->getRelationName())) {
            $loaded = $model->getRelation($relation->getRelationName());

            // If there's already a tenant, and it isn't the current one, there's
            // an issue
            if ($loaded->getTenantKey() !== $tenant->getTenantKey()) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        } else {
            $foreignKey = $localKey = null;

            if ($relation instanceof BelongsTo) {
                $foreignKey = $model->getAttribute($relation->getForeignKeyName());
                $localKey   = $tenant->getTenantKey();
            } else if ($relation instanceof HasOne) {
                $foreignKey = $tenant->getAttribute($relation->getForeignKeyName());
                $localKey   = $model->getKey();
            } else if ($relation instanceof MorphOne) {
                $foreignKey = $tenant->getAttribute($relation->getForeignKeyName());
                $localKey   = $model->getKey();

                // If the morph is for a different class, there's an issue
                if ($relation->getMorphClass() !== $tenant::class) {
                    throw new RuntimeException('Model tenant is not current tenant');
                }
            }

            // If there's no key, it's orphaned
            if ($foreignKey === null) {
                throw new RuntimeException('Orphaned tenant owned model');
            }

            // If there is a key, and it doesn't match the current tenant,
            // there's an issue
            if ($foreignKey !== $localKey) {
                throw new RuntimeException('Model tenant is not current tenant');
            }
        }

        // If there are no issues, populate the relation
        $model->setRelation($relation->getRelationName(), $tenant);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model                                 $model
     * @param \Illuminate\Database\Eloquent\Relations\BelongsToMany               $relation
     * @param \Illuminate\Database\Eloquent\Model|\Tenanted\Core\Contracts\Tenant $tenant
     *
     * @return void
     */
    private function populateManyRelation(Model $model, BelongsToMany $relation, Model|Tenant $tenant): void
    {
        // If the tenant is loaded, we'll check
        if ($model->relationLoaded($relation->getRelationName())) {
            /** @var \Illuminate\Database\Eloquent\Collection $loaded */
            $loaded = $model->getRelation($relation->getRelationName());

            // If it's already loaded, we'll check to see if the current tenant
            // is present
            if (! $loaded->pluck($tenant->getTenantKeyName())->contains($tenant->getTenantKey())) {
                throw new RuntimeException('Model does not belong to the current tenant');
            }
        } else {
            $exists = $relation->newPivotQuery()
                               ->where(
                                   $relation->getRelatedPivotKeyName(),
                                   '=',
                                   $tenant->getTenantKey()
                               )->count();

            if ($exists === 0) {
                throw new RuntimeException('Model does not belong to the current tenant');
            }
        }

        // If there are no issues, populate the relation
        $model->setRelation($relation->getRelationName(), $tenant->newCollection([$tenant]));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model                                 $model
     * @param \Illuminate\Database\Eloquent\Relations\HasOneOrMany                $relation
     * @param \Illuminate\Database\Eloquent\Model|\Tenanted\Core\Contracts\Tenant $tenant
     * @param string                                                              $relationName
     *
     * @return void
     */
    private function populateHasManyRelation(Model $model, HasOneOrMany $relation, Model|Tenant $tenant, string $relationName): void
    {
        // If the tenant is loaded, we'll check
        if ($model->relationLoaded($relationName)) {
            /** @var \Illuminate\Database\Eloquent\Collection $loaded */
            $loaded = $model->getRelation($relationName);

            // If it's already loaded, we'll check to see if the current tenant
            // is present
            if (! $loaded->pluck($tenant->getTenantKeyName())->contains($tenant->getTenantKey())) {
                throw new RuntimeException('Model does not belong to the current tenant');
            }
        } else {
            $exists = $relation->getQuery()
                               ->where(
                                   $relation->getForeignKeyName(),
                                   '=',
                                   $tenant->getTenantKey()
                               )->count();

            if ($exists === 0) {
                throw new RuntimeException('Model does not belong to the current tenant');
            }
        }

        // If there are no issues, populate the relation
        $model->setRelation($relationName, $tenant->newCollection([$tenant]));
    }
}