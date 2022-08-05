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
                    'parameters' => [],
                    'input-array-size-limit' => null,
                ],
                'save' => [
                    'resources' => [
                        'Magento_Customer::manage'
                    ],
                    'secure' => false,
                    'realMethod' => 'save',
                    'parameters' => [],
                    'input-array-size-limit' => 50,
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
                    'input-array-size-limit' => null,
                ],
                'deleteById' => [
                    'resources' => [
                        'Magento_Customer::manage',
                        'Magento_Customer::delete',
                    ],
                    'secure' => false,
                    'realMethod' => 'deleteById',
                    'parameters' => [],
                    'input-array-size-limit' => null,
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
                'input-array-size-limit' => null,
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
                'input-array-size-limit' => null,
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
                'input-array-size-limit' => null,
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
                'parameters' => [],
                'input-array-size-limit' => 50,
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
                'parameters' => [],
                'input-array-size-limit' => null,
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
                'parameters' => [],
                'input-array-size-limit' => null,
            ],
        ],
    ],
];
