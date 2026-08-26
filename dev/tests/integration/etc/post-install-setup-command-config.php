<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

// List of bin/magento setup CLI commands to run after setup:install
return [

    [
        'command' => 'setup:config:set',
        'config' => [
            '--cache-backend' => 'redis',
            '--cache-backend-redis-server' => '127.0.0.1',
            '--cache-backend-redis-db' => '3'
        ]
    ],
    [
        'command' => 'setup:config:set',
        'config' => [
            '--page-cache' => 'redis',
            '--page-cache-redis-server' => '127.0.0.1',
            '--page-cache-redis-db' => '4'
        ]
    ],
];
