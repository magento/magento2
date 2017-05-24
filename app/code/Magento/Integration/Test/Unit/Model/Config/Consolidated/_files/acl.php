<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    [],
    [
        'children' =>
            [
                [
                    'id' => 'Magento_Customer::manageParent',
                    'title' => 'Magento Webapi',
                    'children' => [
                        [
                            'id' => 'Magento_Customer::manage',
                            'title' => 'Manage Customers',
                            'sortOrder' => 20,
                            'children' => [
                                [
                                    'id' => 'Magento_Customer::manageChild',
                                    'title' => 'Create Customer',
                                    'sortOrder' => '30',
                                    'children' => [],
                                ],
                                [
                                    'id' => 'Magento_Customer::manageChild2',
                                    'title' => 'Edit Customer',
                                    'sortOrder' => '10',
                                    'children' => []
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'Magento_SalesRule::quoteParent',
                    'title' => 'Magento Webapi',
                    'children' => [
                        [
                            'id' => 'Magento_SalesRule::quote',
                            'title' => 'Manage Customers',
                            'sortOrder' => 20,
                            'children' => [
                                [
                                    'id' => 'Magento_SalesRule::quoteChild',
                                    'title' => 'Create Customer',
                                    'sortOrder' => '30',
                                    'children' => [],
                                ],
                                [
                                    'id' => 'Magento_SalesRule::quoteChild2',
                                    'title' => 'Edit Customer',
                                    'sortOrder' => '10',
                                    'children' => []
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'Magento_Sales::sales',
                    'title' => 'Magento Webapi',
                    'children' => []
                ]
            ]
    ]
];
