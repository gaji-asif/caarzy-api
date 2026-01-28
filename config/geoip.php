<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Log failures when a location is not found for the IP provided.
    |
    */
    'log_failures' => true,

    /*
    |--------------------------------------------------------------------------
    | Include Currency in Results
    |--------------------------------------------------------------------------
    */
    'include_currency' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Service
    |--------------------------------------------------------------------------
    |
    | Use MaxMind Web API service.
    |
    */
    'service' => 'maxmind_api',

    /*
    |--------------------------------------------------------------------------
    | Storage Specific Configuration
    |--------------------------------------------------------------------------
    */
    'services' => [

        'maxmind_api' => [
            'class' => \Torann\GeoIP\Services\MaxMindWebService::class,
            'user_id' => env('MAXMIND_USER_ID'),        // Add your MaxMind User ID in .env
            'license_key' => env('MAXMIND_LICENSE_KEY'), // Add your MaxMind License Key in .env
            'locales' => ['en'],
        ],

        // Optional: you can keep other services if needed
        'ipgeolocation' => [
            'class' => \Torann\GeoIP\Services\IPGeoLocation::class,
            'secure' => true,
            'key' => env('IPGEOLOCATION_KEY'),
            'continent_path' => storage_path('app/continents.json'),
            'lang' => 'en',
        ],

        'ipdata' => [
            'class' => \Torann\GeoIP\Services\IPData::class,
            'key' => env('IPDATA_API_KEY'),
            'secure' => true,
        ],

        'ipfinder' => [
            'class' => \Torann\GeoIP\Services\IPFinder::class,
            'key' => env('IPFINDER_API_KEY'),
            'secure' => true,
            'locales' => ['en'],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    | Use Redis or compatible cache driver for Windows.
    */
    'cache' => 'all',
    'cache_tags' => ['torann-geoip-location'],
    'cache_expires' => 30, // minutes

    /*
    |--------------------------------------------------------------------------
    | Default Location
    |--------------------------------------------------------------------------
    | Used when IP detection fails. Set to Finland as default.
    */
    'default_location' => [
        'ip' => '127.0.0.1',
        'iso_code' => 'FI',
        'country' => 'Finland',
        'city' => 'Helsinki',
        'state' => '',
        'state_name' => '',
        'postal_code' => '',
        'lat' => 60.1695,
        'lon' => 24.9354,
        'timezone' => 'Europe/Helsinki',
        'continent' => 'EU',
        'default' => true,
        'currency' => 'EUR',
    ],

];
