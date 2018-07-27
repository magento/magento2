<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    [
        'id' => 'One_Module::resource',
        'title' => 'Resource One',
        'sortOrder' => 10,
        'disabled' => false,
        'children' => [],
    ],
    [
        'id' => 'One_Module::resource_one',
        'title' => 'Resource Two',
        'sortOrder' => 30,
        'disabled' => true,
        'children' => []
    ],
    [
        'id' => 'One_Module::resource_parent',
        'title' => 'Resource Parent',
        'sortOrder' => 25,
        'disabled' => false,
        'children' => [
            [
                'id' => 'One_Module::resource_child_one',
                'title' => 'Resource Child',
                'sortOrder' => 15,
                'disabled' => false,
                'children' => [
                    [
                        'id' => 'One_Module::resource_child_two',
                        'title' => 'Child Resource Level 2 Title',
                        'sortOrder' => 40,
                        'disabled' => false,
                        'children' => [],
                    ],
                ],
            ],
        ]
    ]
];
