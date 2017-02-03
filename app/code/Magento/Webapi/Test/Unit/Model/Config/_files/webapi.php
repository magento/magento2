<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'services' => [
        'Magento\Customer\Api\CustomerRepositoryInterface' => [
            'V1' => [
                'methods' => [
                    'getById' => [
                        'resources' => [
                            'Magento_Customer::customer_self',
                            'Magento_Customer::read',
                        ],
                        'secure' => false,
                    ],
                    'save' => [
                        'resources' => [
                            'Magento_Customer::customer_self',
                            'Magento_Customer::manage'
                        ],
                        'secure' => true,
                    ],
                    'deleteById' => [
                        'resources' => [
                            'Magento_Customer::manage',
                            'Magento_Customer::delete',
                        ],
                        'secure' => false,
                    ],
                ],
            ],
        ],
    ],
    'routes' => [
        '/V1/customers/me/session' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                    'method' => 'getById',
                ],
                'resources' => [
                    'Magento_Customer::customer_self' => true,
                ],
                'parameters' => [
                    'id' => [
                        'force' => true,
                        'value' => '%customer_id%',
                    ],
                ],
            ],
        ],
        '/V1/customers/me' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                    'method' => 'getById',
                ],
                'resources' => [
                    'Magento_Customer::customer_self' => true,
                ],
                'parameters' => [
                    'id' => [
                        'force' => true,
                        'value' => null,
                    ],
                ],
            ],
            'PUT' => [
                'secure' => true,
                'service' => [
                    'class' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                    'method' => 'save',
                ],
                'resources' => [
                    'Magento_Customer::customer_self' => true,
                ],
                'parameters' => [
                    'id' => [
                        'force' => false,
                        'value' => null,
                    ],
                ],
            ],
        ],
        '/V1/customers' => [
            'POST' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                    'method' => 'save',
                ],
                'resources' => [
                    'Magento_Customer::manage' => true,
                ],
                'parameters' => [
                ],
            ],
        ],
        '/V1/customers/:id' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                    'method' => 'getById',
                ],
                'resources' => [
                    'Magento_Customer::read' => true,
                ],
                'parameters' => [
                ],
            ],
            'DELETE' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\Customer\Api\CustomerRepositoryInterface',
                    'method' => 'deleteById',
                ],
                'resources' => [
                    'Magento_Customer::manage' => true,
                    'Magento_Customer::delete' => true,
                ],
                'parameters' => [
                ],
            ],
        ],
    ],
];
