<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    [
        'command' => 'setup:db-schema:add-slave',
        'host' => '{{db-host}}',
        'dbname' => '{{db-name}}',
        'username' => '{{db-user}}',
        'password' => '{{db-password}}',
    ],
];
