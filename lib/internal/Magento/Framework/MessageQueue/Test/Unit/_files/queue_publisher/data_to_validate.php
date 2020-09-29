<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'top04' => [
        'topic' => 'top04',
        'disabled' => false,
        'connections' => [
            'amqp' => ['name' => 'amqp', 'exchange' => 'magento8', 'disabled' => true],
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
    'top06' => [
        'topic' => 'top06',
        'disabled' => false,
        'connections' => [
            'amqp' => ['name' => 'amqp', 'exchange' => 'magento', 'disabled' => false]
        ]
    ],
    'top07' => [
        'topic' => 'top07',
        'disabled' => false,
        'connections' => [
            'amqp' => ['name' => 'amqp', 'exchange' => 'magento', 'disabled' => false],
            'db' => ['name' => 'db', 'exchange' => 'exch02', 'disabled' => true],
        ]
    ],
];
