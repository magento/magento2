<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    0 => ['id' => 'One_Module::resource', 'title' => 'Resource One', 'sortOrder' => 10, 'children' => []],
    1 => [
        'id' => 'One_Module::resource_parent',
        'title' => 'Resource Parent',
        'sortOrder' => 25,
        'children' => [
            0 => [
                'id' => 'One_Module::resource_child_one',
                'title' => 'Resource Child',
                'sortOrder' => 15,
                'children' => [
                    0 => [
                        'id' => 'One_Module::resource_child_two',
                        'title' => 'Child Resource Level 2 Title',
                        'sortOrder' => 40,
                        'children' => [],
                    ],
                ],
            ],
        ],
    ]
];
