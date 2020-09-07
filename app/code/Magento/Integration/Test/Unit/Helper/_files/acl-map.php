<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    [
        'attr' => [
            'data-id' => 'Magento_Webapi',
        ],
        'data' => 'Magento Webapi',
        'children' => [
            [
                'attr' => [
                    'data-id' => 'customer',
                ],
                'data' => 'Manage Customers',
                'children' => [
                    [
                        'attr' => [
                            'data-id' => 'customer/create',
                        ],
                        'data' => 'Create Customer',
                        'children' => [],
                        'state' => 'open',
                    ],
                    [
                        'attr' => [
                            'data-id' => 'customer/update',
                        ],
                        'data' => 'Edit Customer',
                        'children' => [],
                        'state' => 'open',
                    ],
                    [
                        'attr' => [
                            'data-id' => 'customer/get',
                        ],
                        'data' => 'Get Customer',
                        'children' => [],
                        'state' => 'open',
                    ],
                    [
                        'attr' => [
                            'data-id' => 'customer/delete',
                        ],
                        'data' => 'Delete Customer',
                        'children' => [],
                        'state' => 'open',
                    ],
                ],
                'state' => 'open',
            ],
        ],
        'state' => 'open',
    ]
];
