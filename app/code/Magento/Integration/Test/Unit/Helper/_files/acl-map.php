<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    [
        'id' => 'Magento_Webapi',
        'li_attr' =>  [
            'data-id' => 'Magento_Webapi',
        ],
        'text' => __('Magento Webapi'),
        'children' => [
            [
                'id' => 'customer',
                'li_attr' =>  [
                    'data-id' => 'customer',
                ],
                'text' => __('Manage Customers'),
                'children' => [
                    [
                        'id' => 'customer/create',
                        'li_attr' =>  [
                            'data-id' => 'customer/create',
                        ],
                        'text' => __('Create Customer'),
                        'children' => [],
                        'state' => [
                            'selected' => false,
                            'opened' => true,
                        ],
                    ],
                    [
                        'id' => 'customer/update',
                        'li_attr' =>  [
                            'data-id' => 'customer/update',
                        ],
                        'text' => __('Edit Customer'),
                        'children' => [],
                        'state' => [
                            'selected' => false,
                            'opened' => true,
                        ],
                    ],
                    [
                        'id' => 'customer/get',
                        'li_attr' =>  [
                            'data-id' => 'customer/get',
                        ],
                        'text' => __('Get Customer'),
                        'children' => [],
                        'state' => [
                            'selected' => false,
                            'opened' => true,
                        ],
                    ],
                    [
                        'id' => 'customer/delete',
                        'li_attr' =>  [
                            'data-id' => 'customer/delete',
                        ],
                        'text' => __('Delete Customer'),
                        'children' => [],
                        'state' => [
                            'selected' => false,
                            'opened' => true,
                        ],
                    ],
                ],
                'state' => [
                    'selected' => false,
                    'opened' => true,
                ],
            ],
        ],
        'state' => [
            'selected' => false,
            'opened' => true,
        ],
    ]
];
