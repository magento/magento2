<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'top04' => [
        'topic' => 'top04',
        'connections' => [
            'amqp' => ['name' => 'amqp', 'disabled' => true],
            'db' => ['name' => 'db', 'exchange' => 'magento2', 'disabled' => false]
        ]
    ],
    'top05' => [
        'topic' => 'top05',
        'disabled' => false,
        'connections' => [
            'amqp' => ['name' => 'amqp', 'exchange' => 'exch01', 'disabled' => false],
            'db' => ['name' => 'db', 'exchange' => 'exch02', 'disabled' => true],
        ]
    ],
    'top07' => [
        'topic' => 'top07',
        'disabled' => false,
        'connections' => [
            'amqp' => ['name' => 'amqp', 'exchange' => 'exch01', 'disabled' => true],
            'db' => ['name' => 'db', 'exchange' => 'exch02', 'disabled' => true],
        ]
    ],
];
