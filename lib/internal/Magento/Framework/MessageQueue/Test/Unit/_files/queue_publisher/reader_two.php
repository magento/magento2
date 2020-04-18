<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'top04' => [
        'topic' => 'top04',
        'connection' => ['name' => 'db', 'exchange' => 'magento2', 'disabled' => false],
    ],
    'top05' => [
        'topic' => 'top05',
        'disabled' => false,
        'connection' => ['name' => 'amqp', 'exchange' => 'exch01', 'disabled' => false],
    ],
    'top07' => [
        'topic' => 'top07',
        'disabled' => false,
        'connection' => ['name' => 'db', 'exchange' => 'exch02', 'disabled' => true],
    ],
];
