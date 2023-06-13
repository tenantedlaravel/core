<?php

use App\Models\Project;
use Tenanted\Core\Features;
use Tenanted\Core\Support\TenantedHelper;

return [

    /*
    |--------------------------------------------------------------------------
    | Tenanted Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default tenancy, tenanted provider and resolver
    | options for your application. You may change these defaults as required,
    | but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'provider' => 'project',
        'tenancy'  => 'primary',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Providers
    |--------------------------------------------------------------------------
    |
    | This defines how the tenants are actually retrieved out of your database
    | or other storage mechanism used by this application to persist your
    | tenants' data.
    |
    | You can define more than one provider for the same data, such as one
    | that works with Eloquent, and another the database.
    |
    | Supported: "eloquent", "database", "array"
    |
    */

    'providers' => [

        'project' => [
            'driver' => 'eloquent',
            'model'  => Project::class,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolvers
    |--------------------------------------------------------------------------
    |
    | This defines how the tenants are resolved during a request.
    |
    | You can define more than one resolver, and have tenants resolvers in
    | different ways for different requests.
    |
    | Supported: "subdomain", "domain", "path", "header", "session", "auth"
    |
    */

    'resolvers' => [

        'subdomain' => [
            'driver' => 'subdomain',
            'domain' => env('APP_TENANT_DOMAIN', 'localhost'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | This defines the enabled features of the package.
    |
    */

    'features' => [

        Features\NotFoundOnInactive::class,
        Features\TenantedDatabaseConnections::class,

    ],

];