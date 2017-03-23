<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'db' => [
        'connection' => [
            'connection_one' => ['name' => 'connection_one', 'dbname' => 'overridden_db_one'],
            'connection_two' => ['name' => 'connection_two', 'dbname' => 'db_two'],
            'connection_new' => ['name' => 'connection_new', 'dbname' => 'db_new'],
        ],
    ],
    'resource' => [
        'resource_one' => ['name' => 'resource_one', 'connection' => 'connection_new'],
        'resource_two' => ['name' => 'resource_two', 'connection' => 'connection_two'],
        'resource_new' => ['name' => 'resource_new', 'connection' => 'connection_two'],
    ],
    'cache' => [
        'frontend' => [
            'cache_frontend_one' => ['name' => 'cache_frontend_one', 'backend' => 'memcached'],
            'cache_frontend_two' => ['name' => 'cache_frontend_two', 'backend' => 'file'],
            'cache_frontend_new' => ['name' => 'cache_frontend_new', 'backend' => 'apc'],
        ],
        'type' => [
            'cache_type_one' => ['name' => 'cache_type_one', 'frontend' => 'cache_frontend_new'],
            'cache_type_two' => ['name' => 'cache_type_two', 'frontend' => 'cache_frontend_two'],
            'cache_type_new' => ['name' => 'cache_type_new', 'frontend' => 'cache_frontend_two'],
        ],
    ],
    'arbitrary_one' => 'Overridden Value One',
    'arbitrary_two' => 'Value Two',
    'huge_nested_level' => [
        'level_one' => [
            'level_two' => ['level_three' => ['level_four' => ['level_five' => 'Level Five Data']]],
        ],
    ],
    'arbitrary_new' => 'Value New'
];
