<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'event_1' => [
        'observer_1' => ['instance' => 'instance_1', 'name' => 'observer_1'],
        'observer_5' => ['instance' => 'instance_5', 'name' => 'observer_5'],
    ],
    'event_2' => [
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
