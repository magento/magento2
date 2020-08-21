<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'db' => [
        'connection' => [
            'connection_one' => ['name' => 'connection_one', 'dbname' => 'overridden_db_one'],
            'connection_new' => ['name' => 'connection_new', 'dbname' => 'db_new'],
        ],
    ],
    'resource' => [
        'resource_one' => ['name' => 'resource_one', 'connection' => 'connection_new'],
        'resource_new' => ['name' => 'resource_new', 'connection' => 'connection_two'],
    ],
    'cache' => [
        'frontend' => [
            'cache_frontend_one' => ['name' => 'cache_frontend_one', 'backend' => 'memcached'],
            'cache_frontend_new' => ['name' => 'cache_frontend_new', 'backend' => 'apc'],
        ],
        'type' => [
            'cache_type_one' => ['name' => 'cache_type_one', 'frontend' => 'cache_frontend_new'],
            'cache_type_new' => ['name' => 'cache_type_new', 'frontend' => 'cache_frontend_two'],
        ],
    ],
    'arbitrary_one' => 'Overridden Value One',
    'arbitrary_new' => 'Value New'
];
