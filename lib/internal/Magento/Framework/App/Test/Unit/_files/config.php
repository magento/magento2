<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'db' => [
        'connection' => [
            'connection_one' => ['name' => 'connection_one', 'dbname' => 'db_one'],
            'connection_two' => ['name' => 'connection_two', 'dbname' => 'db_two'],
        ],
    ],
    'resource' => [
        'resource_one' => ['name' => 'resource_one', 'connection' => 'connection_one'],
        'resource_two' => ['name' => 'resource_two', 'connection' => 'connection_two'],
    ],
    'cache' => [
        'frontend' => [
            'cache_frontend_one' => ['name' => 'cache_frontend_one', 'backend' => 'blackHole'],
            'cache_frontend_two' => ['name' => 'cache_frontend_two', 'backend' => 'file'],
        ],
        'type' => [
            'cache_type_one' => ['name' => 'cache_type_one', 'frontend' => 'cache_frontend_one'],
            'cache_type_two' => ['name' => 'cache_type_two', 'frontend' => 'cache_frontend_two'],
        ],
    ],
    'arbitrary_one' => 'Value One',
    'arbitrary_two' => 'Value Two',
    'huge_nested_level' => [
        'level_one' => [
            'level_two' => ['level_three' => ['level_four' => ['level_five' => 'Level Five Data']]],
        ],
    ]
];
