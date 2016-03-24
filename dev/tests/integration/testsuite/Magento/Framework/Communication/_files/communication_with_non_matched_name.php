<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'communication' => [
        'topics' => [
            'customerDeleted' => [
                'name' => 'customerRemoved',
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
                ],
            ],
        ]
    ]
];
