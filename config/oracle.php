<?php

return [
    'oracle' => [
        'driver'        => 'oracle',
        'tns'           => env('DB_TNS', ''),
        'host'          => env('DB_HOST', '172.23.50.95'),
        'port'          => env('DB_PORT', '1521'),
        'database'      => env('DB_DATABASE', 'CATGIS'),
        'username'      => env('DB_USERNAME', 'RRHH'),
        'password'      => env('DB_PASSWORD', 'rrhhadmin'),
        'charset'       => env('DB_CHARSET', 'AL32UTF8'),
        'prefix'        => env('DB_PREFIX', ''),
        'prefix_schema' => env('DB_SCHEMA_PREFIX', ''),
        // 'edition'       => env('DB_EDITION', 'ora$base'),
    ],

    'portales' => [
        'driver'        => 'oracle',
        'tns'           => env('DB_TNS', ''),
        'host'          => env('DB_HOST', '172.23.50.95'),
        'port'          => env('DB_PORT', '1521'),
        'database'      => env('DB_DATABASE', 'CATGIS'),
        'username'      => env('DB_USERNAME2', 'PORTALES'),
        'password'      => env('DB_PASSWORD2', 'portales2014'),
        'charset'       => env('DB_CHARSET', 'AL32UTF8'),
        'prefix'        => env('DB_PREFIX', ''),
        'prefix_schema' => env('DB_SCHEMA_PREFIX', ''),
        // 'edition'       => env('DB_EDITION', 'ora$base'),
    ],

    'catastrousr' => [
        'driver'        => 'oracle',
        'tns'           => env('DB_TNS', ''),
        'host'          => env('DB_HOST', '172.23.50.95'),
        'port'          => env('DB_PORT', '1521'),
        'database'      => env('DB_DATABASE', 'CATGIS'),
        'username'      => env('DB_USERNAME3', 'CATASTROUSR'),
        'password'      => env('DB_PASSWORD3', 'k4t4str03d'),
        'charset'       => env('DB_CHARSET', 'AL32UTF8'),
        'prefix'        => env('DB_PREFIX', ''),
        'prefix_schema' => env('DB_SCHEMA_PREFIX', ''),
        // 'edition'       => env('DB_EDITION', 'ora$base'),
    ],

    'cobros' => [
        'driver'        => 'oracle',
        'tns'           => env('DB_TNS', ''),
        'host'          => env('DB_HOST', '172.23.50.95'),
        'port'          => env('DB_PORT', '1521'),
        'database'      => env('DB_DATABASE', 'CATGIS'),
        'username'      => env('DB_USERNAME3', 'cobros_iusi'),
        'password'      => env('DB_PASSWORD3', 'cobrosiusi'),
        'charset'       => env('DB_CHARSET', 'AL32UTF8'),
        'prefix'        => env('DB_PREFIX', ''),
        'prefix_schema' => env('DB_SCHEMA_PREFIX', ''),
        // 'edition'       => env('DB_EDITION', 'ora$base'),
    ],
];
