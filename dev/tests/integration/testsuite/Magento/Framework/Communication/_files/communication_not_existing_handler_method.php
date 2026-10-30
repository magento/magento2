<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

return [
    'communication' => [
        'topics' => [
            'customerAdded' => [
                'name' => 'customerAdded',
                'is_synchronous' => false,
                'request' => \Magento\Customer\Api\Data\CustomerInterface::class,
                'request_type' => 'object_interface',
                'response' => null,
                'handlers' => [
                    'customerCreatedFirst' => [
                        'type' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
                        'method' => 'invalid',
                    ],
                    'customerCreatedSecond' => [
                        'type' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
                        'method' => 'delete',
                    ],
                    'saveNameNotDisabled' => [
                        'type' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
                        'method' => 'save',
                    ],
                    'saveNameNotDisabledDigit' => [
                        'type' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
                        'method' => 'save',
                    ],
                ],
            ],
        ]
    ]
];
