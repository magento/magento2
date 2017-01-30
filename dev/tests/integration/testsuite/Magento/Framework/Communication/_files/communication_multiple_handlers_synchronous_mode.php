<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
                        'param_type' => 'Magento\Customer\Api\Data\CustomerInterface',
                    ],
                ],
                'request_type' => 'service_method_interface',
                'response' => 'bool',
                'handlers' => [
                    'customHandler' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'deleteById',
                    ],
                    'defaultHandler' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'get',
                    ],
                ],
            ],
        ]
    ]
];
