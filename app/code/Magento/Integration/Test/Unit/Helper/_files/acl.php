<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    [
        'id' => 'Magento_Webapi',
        'title' => 'Magento Webapi',
        'children' => [
            [
                'id' => 'customer',
                'title' => 'Manage Customers',
                'sortOrder' => 20,
                'children' => [
                    [
                        'id' => 'customer/create',
                        'title' => 'Create Customer',
                        'sortOrder' => '30',
                        'children' => [],
                    ],
                    [
                        'id' => 'customer/update',
                        'title' => 'Edit Customer',
                        'sortOrder' => '10',
                        'children' => []
                    ],
                    [
                        'id' => 'customer/get',
                        'title' => 'Get Customer',
                        'sortOrder' => '20',
                        'children' => []
                    ],
                    ['id' => 'customer/delete', 'title' => 'Delete Customer', 'children' => []],
                ],
            ],
        ],
    ]
];
