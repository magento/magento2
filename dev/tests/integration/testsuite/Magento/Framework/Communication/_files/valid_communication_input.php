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
                'is_synchronous' => true,
                'request' => 'Magento\Customer\Api\Data\CustomerInterface',
                'request_type' => 'object_interface',
                'response' => 'Magento\Customer\Api\Data\CustomerInterface',
                'handlers' => [
                    'default' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'save',
                    ],
                ],
            ],
            'customerAdded' => [
                'name' => 'customerAdded',
                'is_synchronous' => false,
                'request' => 'string[]',
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
            'customerUpdated' => [
                'name' => 'customerUpdated',
                'is_synchronous' => true,
                'request' => 'Magento\Customer\Api\Data\CustomerInterface',
                'request_type' => 'object_interface',
                'response' => 'Magento\Customer\Api\Data\CustomerInterface[]',
                'handlers' => [
                    'updateName' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'save',
                    ],
                ],
            ],
            'customerModified' => [
                'name' => 'customerModified',
                'is_synchronous' => false,
                'request' => 'Magento\Customer\Api\Data\CustomerInterface',
                'request_type' => 'object_interface',
                'response' => null,
                'handlers' => [
                    'updateName' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'save',
                    ],
                ],
            ],
            'customerRetrieved' => [
                'name' => 'customerRetrieved',
                'is_synchronous' => true,
                'request' => [
                    [
                        'param_name' => 'email',
                        'param_position' => 0,
                        'is_required' => true,
                        'param_type' => 'string',
                    ],
                    [
                        'param_name' => 'websiteId',
                        'param_position' => 1,
                        'is_required' => false,
                        'param_type' => 'int',
                    ],
                ],
                'request_type' => 'service_method_interface',
                'response' => '\Magento\Customer\Api\Data\CustomerInterface',
                'handlers' => [
                    'defaultHandler' => [
                        'type' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                        'method' => 'get',
                    ],
                ],
            ],
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
                ],
            ],
        ],
    ]
];
