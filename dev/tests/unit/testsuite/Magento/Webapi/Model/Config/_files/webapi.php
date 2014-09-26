<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return [
    'services' => [
        'Magento\Customer\Service\V1\CustomerServiceInterface' => [
            'getCustomer' => [
                'resources' => [
                    'Magento_Customer::customer_self',
                    'Magento_Customer::read',
                ],
                'secure' => false,
            ],
            'updateCustomer' => [
                'resources' => ['Magento_Customer::customer_self'],
                'secure' => true,
            ],
            'createCustomer' => [
                'resources' => ['Magento_Customer::manage'],
                'secure' => false,
            ],
            'deleteCustomer' => [
                'resources' => [
                    'Magento_Customer::manage',
                    'Magento_Customer::delete'
                ],
                'secure' => false,
            ],
        ],
    ],
    'routes' => [
        '/V1/customers/me/session' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\Customer\Service\V1\CustomerServiceInterface',
                    'method' => 'getCustomer',
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
                    'class' => 'Magento\Customer\Service\V1\CustomerServiceInterface',
                    'method' => 'getCustomer',
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
                    'class' => 'Magento\Customer\Service\V1\CustomerServiceInterface',
                    'method' => 'updateCustomer',
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
            ]
        ],
        '/V1/customers' => [
            'POST' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\Customer\Service\V1\CustomerServiceInterface',
                    'method' => 'createCustomer',
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
                    'class' => 'Magento\Customer\Service\V1\CustomerServiceInterface',
                    'method' => 'getCustomer',
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
                    'class' => 'Magento\Customer\Service\V1\CustomerServiceInterface',
                    'method' => 'deleteCustomer',
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
