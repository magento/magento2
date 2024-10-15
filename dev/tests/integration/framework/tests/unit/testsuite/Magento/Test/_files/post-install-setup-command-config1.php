<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    [
        'command' => 'setup:db-schema:add-slave',
        'config' => [
            '--host' => '/tmp/mysql.sock',
            '--dbname' => 'magento_replica',
            '--username' => 'root',
            '--password' => 'secret',
        ]
    ],
];
