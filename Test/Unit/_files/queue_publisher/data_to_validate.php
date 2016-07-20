<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'top04' => [
        'topic' => 'top04',
        'disabled' => false,
        'connections' => [
            'con01' => ['name' => 'con01', 'exchange' => 'magento8', 'disabled' => true],
            'con02' => ['name' => 'con02', 'exchange' => 'magento2', 'disabled' => false]
        ]
    ],
    'top05' => [
        'topic' => 'top05',
        'disabled' => false,
        'connections' => [
            'con01' => ['name' => 'con01', 'exchange' => 'exch01', 'disabled' => false],
            'con02' => ['name' => 'con02', 'exchange' => 'exch02', 'disabled' => true],
            'con03' => ['name' => 'con03', 'exchange' => 'magento', 'disabled' => true]
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
            'con01' => ['name' => 'con01', 'exchange' => 'exch01', 'disabled' => true],
            'con02' => ['name' => 'con02', 'exchange' => 'exch02', 'disabled' => true],
            'con03' => ['name' => 'con03', 'exchange' => 'magento', 'disabled' => true],
            'amqp' => ['name' => 'amqp', 'exchange' => 'magento', 'disabled' => false]
        ]
    ],
];
