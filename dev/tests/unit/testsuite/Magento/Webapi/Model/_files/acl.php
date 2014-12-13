<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
return [
    [
        'id' => 'Magento_Webapi',
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
