<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'communication' => [
        'topics' => [
            'customerDeleted' => [
                'name' => 'customerDeleted',
                'is_synchronous' => true,
                'request' => [
                    [
                        'param_name' => 'customer',
                        'param_position' => 0,
                        'is_required' => true,
                        'param_type' => \Magento\Customer\Api\Data\CustomerInterface::class,
                    ],
                ],
                'request_type' => 'service_method_interface',
                'response' => 'bool',
                'handlers' => [
                    'customHandler' => [
                        'type' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
                        'method' => 'deleteById',
                    ],
                    'defaultHandler' => [
                        'type' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
                        'method' => 'get',
                    ],
                ],
            ],
        ]
    ]
];
