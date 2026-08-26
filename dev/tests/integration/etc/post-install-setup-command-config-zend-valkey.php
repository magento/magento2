<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
return [
    [
        'command' => 'setup:config:set',
        'config' => [
            '--cache-backend' => 'valkey',
            '--cache-backend-valkey-server' => '127.0.0.1',
            '--cache-backend-valkey-db' => '3'
        ]
    ],
    [
        'command' => 'setup:config:set',
        'config' => [
            '--page-cache' => 'valkey',
            '--page-cache-valkey-server' => '127.0.0.1',
            '--page-cache-valkey-db' => '4'
        ]
    ],
];
