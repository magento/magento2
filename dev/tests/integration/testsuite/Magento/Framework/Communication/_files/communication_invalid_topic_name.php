<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'communication' => [
        'topics' => [
            'customerCreated' => [
                'name' => 'customerCreated',
                'is_synchronous' => false,
                'request' => 'Magento\Customer\Api\Data\CustomerInterface',
                'request_type' => 'object_interface',
                'response' => null,
                'handlers' => [],
            ],
            'customerAdded' => [
                'name' => 'customerCreated',
                'is_synchronous' => false,
                'request' => 'Magento\Customer\Api\Data\CustomerInterface',
                'request_type' => 'object_interface',
                'response' => null,
                'handlers' => [
                    'customerCreatedFirst' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'save',
                    ],
                    'customerCreatedSecond' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'delete',
                    ],
                    'saveNameNotDisabled' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'save',
                    ],
                    'saveNameNotDisabledDigit' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'save',
                    ],
                ],
            ],
        ]
    ]
];
