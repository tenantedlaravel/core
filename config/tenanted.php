<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenanted Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default tenanted provider and resolver options
    | for your application. You may change these defaults as required, but
    | they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'provider' => 'project',
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
            'model'  => \App\Models\Project::class,
        ],

        // Example database config
        //
        // 'project' => [
        //     'driver'     => 'database',
        //     'connection' => env('DB_CONNECTION'),
        //     'table'      => 'organisations',
        //     'identifier' => 'identifier',
        //    'key'        => 'id',
        //     'entity'     => \Tenanted\Core\Support\TenantEntity::class,
        // ],

        // Example array config
        //
        // 'project' => [
        //     'driver'     => 'array',
        //     'source'     => [
        //         'type' => 'include',
        //         'path' => config_path('tenants.php'),
        //     ],
        //     'identifier' => 'identifier',
        //     'key'        => 'id',
        //     'entity'     => \Tenanted\Core\Support\TenantEntity::class,
        // ],

    ],

];