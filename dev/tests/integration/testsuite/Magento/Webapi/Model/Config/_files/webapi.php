<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'services' => [
        'Magento\TestModule1\Service\V1\AllSoapAndRestInterface' => [
            'item' => [
                'resources' => [
                    'Magento_Test1::resource1',
                ],
                'secure' => false,
            ],
            'create' => [
                'resources' => [
                    'Magento_Test1::resource1',
                ],
                'secure' => false,
            ],
        ],
        'Magento\TestModule1\Service\V2\AllSoapAndRestInterface' => [
            'item' => [
                'resources' => [
                    'Magento_Test1::resource1',
                    'Magento_Test1::resource2',
                ],
                'secure' => false,
            ],
            'create' => [
                'resources' => [
                    'Magento_Test1::resource1',
                    'Magento_Test1::resource2',
                ],
                'secure' => false,
            ],
            'delete' => [
                'resources' => [
                    'Magento_Test1::resource1',
                    'Magento_Test1::resource2',
                ],
                'secure' => false,
            ],
            'update' => [
                'resources' => [
                    'Magento_Test1::resource1',
                    'Magento_Test1::resource2',
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
                    ],
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
                    ],
                ],
            ],
        ],
    ],
];
