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
        'Magento\TestModule1\Service\V1\AllSoapAndRestInterface' => [
            'item' => [
                'resources' => [
                    'Magento_Test1::resource1'
                ],
                'secure' => false,
            ],
            'create' => [
                'resources' => [
                    'Magento_Test1::resource1'
                ],
                'secure' => false,
            ],
        ],
        'Magento\TestModule1\Service\V2\AllSoapAndRestInterface' => [
            'item' => [
                'resources' => [
                    'Magento_Test1::resource1',
                    'Magento_Test1::resource2'
                ],
                'secure' => false,
            ],
            'create' => [
                'resources' => [
                    'Magento_Test1::resource1',
                    'Magento_Test1::resource2'
                ],
                'secure' => false,
            ],
            'delete' => [
                'resources' => [
                    'Magento_Test1::resource1',
                    'Magento_Test1::resource2'
                ],
                'secure' => false,
            ],
            'update' => [
                'resources' => [
                    'Magento_Test1::resource1',
                    'Magento_Test1::resource2'
                ],
                'secure' => false,
            ],
        ],
    ],
    'routes' => [
        '/V1/testmodule1/:id' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\TestModule1\Service\V1\AllSoapAndRestInterface',
                    'method' => 'item',
                ],
                'resources' => [
                    'Magento_Test1::resource1' => true,
                ],
                'parameters' => [
                ],
            ],
        ],
        '/V2/testmodule1/:id' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\TestModule1\Service\V2\AllSoapAndRestInterface',
                    'method' => 'item',
                ],
                'resources' => [
                    'Magento_Test1::resource1' => true,
                    'Magento_Test1::resource2' => true,
                ],
                'parameters' => [
                ],
            ],
            'DELETE' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\TestModule1\Service\V2\AllSoapAndRestInterface',
                    'method' => 'delete',
                ],
                'resources' => [
                    'Magento_Test1::resource1' => true,
                    'Magento_Test1::resource2' => true,
                ],
                'parameters' => [
                ],
            ],
            'PUT' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\TestModule1\Service\V2\AllSoapAndRestInterface',
                    'method' => 'update',
                ],
                'resources' => [
                    'Magento_Test1::resource1' => true,
                    'Magento_Test1::resource2' => true,
                ],
                'parameters' => [
                ],
            ],
        ],
        '/V2/testmodule1' => [
            'POST' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\TestModule1\Service\V2\AllSoapAndRestInterface',
                    'method' => 'create',
                ],
                'resources' => [
                    'Magento_Test1::resource1' => true,
                    'Magento_Test1::resource2' => true,
                ],
                'parameters' => [
                    'id' => [
                        'force' => true,
                        'value' => null,
                    ]
                ],
            ],
        ],
        '/V1/testmodule1' => [
            'POST' => [
                'secure' => false,
                'service' => [
                    'class' => 'Magento\TestModule1\Service\V1\AllSoapAndRestInterface',
                    'method' => 'create',
                ],
                'resources' => [
                    'Magento_Test1::resource1' => true,
                ],
                'parameters' => [
                    'id' => [
                        'force' => true,
                        'value' => null,
                    ]
                ],
            ],
        ],
    ],
];
