<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

return [
    'top01' => [
        'topic' => 'top01',
        'queue' => null,
        'disabled' => false,
        'connections' => [
            '' => ['name' => null,
            'exchange' => 'magento',
            'disabled' => false
            ]
        ]
    ],
    'top02' => [
        'topic' => 'top02',
        'queue' => null,
        'disabled' => false,
        'connections' => [
            '' => ['name' => null,
                'exchange' => 'magento',
                'disabled' => false
            ]
        ]
    ],
    'top03' => [
        'topic' => 'top03',
        'queue' => null,
        'disabled' => true,
        'connections' => [
            '' => ['name' => null,
                'exchange' => 'magento',
                'disabled' => false
            ]
        ]
    ],
    'top04' => [
        'topic' => 'top04',
        'queue' => null,
        'disabled' => false,
        'connections' => [
            'amqp' => ['name' => 'amqp', 'exchange' => 'magento', 'disabled' => false]
        ]
    ],
    'top05' => [
        'topic' => 'top05',
        'queue' => null,
        'disabled' => false,
        'connections' => [
            'amqp' => ['name' => 'amqp', 'exchange' => 'exch01', 'disabled' => false],
            'db' => ['name' => 'db', 'exchange' => 'exch02', 'disabled' => true],
        ]
    ],
    'top06' => [
        'topic' => 'top06',
        'queue' => null,
        'disabled' => true,
        'connections' => [
            'amqp' => ['name' => 'amqp', 'exchange' => 'exch01', 'disabled' => false],
            'db' => ['name' => 'db', 'exchange' => 'exch02', 'disabled' => true]
        ]
    ],
];
