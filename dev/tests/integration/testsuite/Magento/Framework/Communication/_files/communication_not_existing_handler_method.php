<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'communication' => [
        'topics' => [
            'customerAdded' => [
                'name' => 'customerAdded',
                'is_synchronous' => false,
                'request' => 'Magento\Customer\Api\Data\CustomerInterface',
                'request_type' => 'object_interface',
                'response' => null,
                'handlers' => [
                    'customerCreatedFirst' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'invalid',
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
