<?php declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'services' => [CustomerRepositoryInterface::class => [
        'V1' => [
            'methods' => [
                'getById' => [
                    'resources' => [
                        'Magento_Customer::customer_self',
                        'Magento_Customer::read',
                    ],
                    'secure' => false,
                    'realMethod' => 'getById',
                    'parameters' => []
                ],
                'save' => [
                    'resources' => [
                        'Magento_Customer::manage'
                    ],
                    'secure' => false,
                    'realMethod' => 'save',
                    'parameters' => []
                ],
                'saveSelf' => [
                    'resources' => [
                        'Magento_Customer::customer_self'
                    ],
                    'secure' => true,
                    'realMethod' => 'save',
                    'parameters' => [
                        'id' => [
                            'force' => false,
                            'value' => null,
                        ],
                    ],
                ],
                'deleteById' => [
                    'resources' => [
                        'Magento_Customer::manage',
                        'Magento_Customer::delete',
                    ],
                    'secure' => false,
                    'realMethod' => 'deleteById',
                    'parameters' => []
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
                    'class' => CustomerRepositoryInterface::class,
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
                    'class' => CustomerRepositoryInterface::class,
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
                    'class' => CustomerRepositoryInterface::class,
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
                    'class' => CustomerRepositoryInterface::class,
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
                    'class' => CustomerRepositoryInterface::class,
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
                    'class' => CustomerRepositoryInterface::class,
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
