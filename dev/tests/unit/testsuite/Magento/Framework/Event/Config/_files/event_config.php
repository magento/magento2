<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'event_1' => [
        'observer_1' => ['instance' => 'instance_1', 'method' => 'method_name_1', 'name' => 'observer_1'],
        'observer_5' => ['instance' => 'instance_5', 'method' => 'method_name_5', 'name' => 'observer_5'],
    ],
    'event_2' => [
        'observer_2' => [
            'instance' => 'instance_2',
            'method' => 'method_name_2',
            'disabled' => true,
            'shared' => false,
            'name' => 'observer_2',
        ],
    ]
];
