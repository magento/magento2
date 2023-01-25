<?php declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    [
        'top.01',
        [
            'is_synchronous' => false,
            'request' => CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => CustomerInterface::class,
        ]
    ],
    [
        'top.03',
        [
            'is_synchronous' => false,
            'request' => CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => CustomerInterface::class,
            'handlers' => [
                'customerCreatedFirst' => [
                    'type' => CustomerRepositoryInterface::class,
                    'method' => 'save',
                ],
                'customerCreatedSecond' => [
                    'type' => CustomerRepositoryInterface::class,
                    'method' => 'delete',
                ],
            ]
        ]
    ],
    [
        'top.04',
        [
            'is_synchronous' => false,
            'request' => CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => CustomerInterface::class,
        ]
    ],
    [
        'top.05',
        [
            'is_synchronous' => false,
            'request' => CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => CustomerInterface::class,
        ]
    ],
    [
        'user.created.remote',
        [
            'is_synchronous' => false,
            'request' => CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => CustomerInterface::class,
        ]
    ],
    [
        'product.created.local',
        [
            'is_synchronous' => false,
            'request' => CustomerInterface::class,
            'request_type' => 'object_interface',
            'response' => CustomerInterface::class,
        ]
    ],
];
