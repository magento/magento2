<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'top01' => [
        'topic' => 'top01',
        'disabled' => false,
        'connections' => []
    ],
    'top02' => [
        'topic' => 'top02',
        'disabled' => false,
        'connections' => []
    ],
    'top03' => [
        'topic' => 'top03',
        'disabled' => true,
        'connections' => []
    ],
    'top04' => [
        'topic' => 'top04',
        'disabled' => false,
        'connections' => [
            'con01' => ['name' => 'con01', 'exchange' => 'magento', 'disabled' => false]
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
        'disabled' => true,
        'connections' => [
            'con01' => ['name' => 'con01', 'exchange' => 'exch01', 'disabled' => false],
            'con02' => ['name' => 'con02', 'exchange' => 'exch02', 'disabled' => true],
            'con03' => ['name' => 'con03', 'exchange' => 'magento', 'disabled' => true]
        ]
    ],
];
