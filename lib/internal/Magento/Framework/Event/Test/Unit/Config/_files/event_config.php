<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'event_one' => [
        'observer_1' => ['instance' => 'instance_1', 'name' => 'observer_1'],
        'observer_5' => ['instance' => 'instance_5', 'name' => 'observer_5'],
    ],
    'event_two' => [
        'observer_2' => [
            'instance' => 'instance_2',
            'disabled' => true,
            'shared' => false,
            'name' => 'observer_2',
        ],
    ],
    'some_eventname' => [
        'observer_3' => ['instance' => 'instance_3', 'name' => 'observer_3'],
    ]
];
