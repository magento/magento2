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
                'is_synchronous' => 3,
                'request' => 'Magento\Customer\Api\Data\CustomerInterface',
                'request_type' => 'object_interface',
                'response' => null,
                'handlers' => [],
            ],
        ]
    ]
];
