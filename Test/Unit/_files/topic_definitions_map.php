<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    [
        'top.01',
        [
            'is_synchronous' => false,
            'request' => 'Magento\Customer\Api\Data\CustomerInterface',
            'request_type' => 'object_interface',
            'response' => 'Magento\Customer\Api\Data\CustomerInterface',
        ]
    ],
    [
        'top.03',
        [
            'is_synchronous' => false,
            'request' => 'Magento\Customer\Api\Data\CustomerInterface',
            'request_type' => 'object_interface',
            'response' => 'Magento\Customer\Api\Data\CustomerInterface',
            'handlers' => [
                'customerCreatedFirst' => [
                    'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                    'method' => 'save',
                ],
                'customerCreatedSecond' => [
                    'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                    'method' => 'delete',
                ],
            ]
        ]
    ],
    [
        'top.04',
        [
            'is_synchronous' => false,
            'request' => 'Magento\Customer\Api\Data\CustomerInterface',
            'request_type' => 'object_interface',
            'response' => 'Magento\Customer\Api\Data\CustomerInterface',
        ]
    ],
    [
        'top.05',
        [
            'is_synchronous' => false,
            'request' => 'Magento\Customer\Api\Data\CustomerInterface',
            'request_type' => 'object_interface',
            'response' => 'Magento\Customer\Api\Data\CustomerInterface',
        ]
    ],
    [
        'user.created.remote',
        [
            'is_synchronous' => false,
            'request' => 'Magento\Customer\Api\Data\CustomerInterface',
            'request_type' => 'object_interface',
            'response' => 'Magento\Customer\Api\Data\CustomerInterface',
        ]
    ],
    [
        'product.created.local',
        [
            'is_synchronous' => false,
            'request' => 'Magento\Customer\Api\Data\CustomerInterface',
            'request_type' => 'object_interface',
            'response' => 'Magento\Customer\Api\Data\CustomerInterface',
        ]
    ],
];
