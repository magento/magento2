<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    [
        'top.01',
        [
            'is_synchronous' => false,
            'request' => \Magento\Customer\Api\Data\CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => \Magento\Customer\Api\Data\CustomerInterface::class,
        ]
    ],
    [
        'top.03',
        [
            'is_synchronous' => false,
            'request' => \Magento\Customer\Api\Data\CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => \Magento\Customer\Api\Data\CustomerInterface::class,
            'handlers' => [
                'customerCreatedFirst' => [
                    'type' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
                    'method' => 'save',
                ],
                'customerCreatedSecond' => [
                    'type' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
                    'method' => 'delete',
                ],
            ]
        ]
    ],
    [
        'top.04',
        [
            'is_synchronous' => false,
            'request' => \Magento\Customer\Api\Data\CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => \Magento\Customer\Api\Data\CustomerInterface::class,
        ]
    ],
    [
        'top.05',
        [
            'is_synchronous' => false,
            'request' => \Magento\Customer\Api\Data\CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => \Magento\Customer\Api\Data\CustomerInterface::class,
        ]
    ],
    [
        'user.created.remote',
        [
            'is_synchronous' => false,
            'request' => \Magento\Customer\Api\Data\CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => \Magento\Customer\Api\Data\CustomerInterface::class,
        ]
    ],
    [
        'product.created.local',
        [
            'is_synchronous' => false,
            'request' => \Magento\Customer\Api\Data\CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => \Magento\Customer\Api\Data\CustomerInterface::class,
        ]
    ],
];
