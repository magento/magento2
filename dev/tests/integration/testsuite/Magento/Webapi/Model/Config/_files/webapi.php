<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'services' => [\Magento\TestModuleMSC\Api\AllSoapAndRestInterface::class => [
            'V1' => [
                'methods' => [
                    'item' => [
                        'resources' => [
                            'Magento_TestModuleMSC::resource1',
                        ],
                        'secure' => false,
                        'realMethod' => 'item',
                        'parameters' => []
                    ],
                    'create' => [
                        'resources' => [
                            'Magento_TestModuleMSC::resource3',
                        ],
                        'secure' => false,
                        'realMethod' => 'create',
                        'parameters' => []
                    ],
                ],
            ],
            'V2' => [
                'methods' => [
                    'getPreconfiguredItem' => [
                        'resources' => [
                            'Magento_TestModuleMSC::resource1',
                            'Magento_TestModuleMSC::resource2',
                        ],
                        'secure' => false,
                        'realMethod' => 'getPreconfiguredItem',
                        'parameters' => []
                    ],
                ],
            ],
        ], \Magento\TestModule1\Service\V1\AllSoapAndRestInterface::class => [
            'V1' => [
                'methods' => [
                    'item' => [
                        'resources' => [
                            'Magento_Test1::resource1',
                        ],
                        'secure' => false,
                        'realMethod' => 'item',
                        'parameters' => []
                    ],
                    'itemDefault' => [
                        'resources' => [
                            'Magento_Test1::default',
                        ],
                        'secure' => false,
                        'realMethod' => 'item',
                        'parameters' => [
                            'id' => [
                                'force' => true,
                                'value' => null,
                            ],
                        ]
                    ],
                    'create' => [
                        'resources' => [
                            'Magento_Test1::resource1',
                        ],
                        'secure' => false,
                        'realMethod' => 'create',
                        'parameters' => [
                            'id' => [
                                'force' => true,
                                'value' => null,
                            ],
                        ]
                    ],
                ],
            ],
        ], \Magento\TestModule1\Service\V2\AllSoapAndRestInterface::class => [
            'V2' => [
                'methods' => [
                    'item' => [
                        'resources' => [
                            'Magento_Test1::resource1',
                            'Magento_Test1::resource2',
                        ],
                        'secure' => false,
                        'realMethod' => 'item',
                        'parameters' => []
                    ],
                    'create' => [
                        'resources' => [
                            'Magento_Test1::resource1',
                            'Magento_Test1::resource2',
                        ],
                        'secure' => false,
                        'realMethod' => 'create',
                        'parameters' => [
                            'id' => [
                                'force' => true,
                                'value' => null,
                            ],
                        ]
                    ],
                    'delete' => [
                        'resources' => [
                            'Magento_Test1::resource1',
                            'Magento_Test1::resource2',
                        ],
                        'secure' => false,
                        'realMethod' => 'delete',
                        'parameters' => []
                    ],
                    'update' => [
                        'resources' => [
                            'Magento_Test1::resource1',
                            'Magento_Test1::resource2',
                        ],
                        'secure' => false,
                        'realMethod' => 'update',
                        'parameters' => []
                    ],
                ],
            ],
        ],
    ],
    'routes' => [
        '/V1/testmoduleMSC/:itemId' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => \Magento\TestModuleMSC\Api\AllSoapAndRestInterface::class,
                    'method' => 'item',
                ],
                'resources' => [
                    'Magento_TestModuleMSC::resource1' => true,
                ],
                'parameters' => [
                ],
            ],
        ],
        '/V1/testmoduleMSC' => [
            'POST' => [
                'secure' => false,
                'service' => [
                    'class' => \Magento\TestModuleMSC\Api\AllSoapAndRestInterface::class,
                    'method' => 'create',
                ],
                'resources' => [
                    'Magento_TestModuleMSC::resource3' => true,
                ],
                'parameters' => [
                ],
            ],
        ],
        '/V1/testmodule1/:id' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => \Magento\TestModule1\Service\V1\AllSoapAndRestInterface::class,
                    'method' => 'item',
                ],
                'resources' => [
                    'Magento_Test1::resource1' => true,
                ],
                'parameters' => [
                ],
            ],
        ],
        '/V1/testmodule1' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => \Magento\TestModule1\Service\V1\AllSoapAndRestInterface::class,
                    'method' => 'item',
                ],
                'resources' => [
                    'Magento_Test1::default' => true,
                ],
                'parameters' => [
                    'id' => [
                        'force' => true,
                        'value' => null,
                    ],
                ],
            ],
            'POST' => [
                'secure' => false,
                'service' => [
                    'class' => \Magento\TestModule1\Service\V1\AllSoapAndRestInterface::class,
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
        '/V2/testmodule1/:id' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => \Magento\TestModule1\Service\V2\AllSoapAndRestInterface::class,
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
                    'class' => \Magento\TestModule1\Service\V2\AllSoapAndRestInterface::class,
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
                    'class' => \Magento\TestModule1\Service\V2\AllSoapAndRestInterface::class,
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
                    'class' => \Magento\TestModule1\Service\V2\AllSoapAndRestInterface::class,
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
        '/V2/testmoduleMSC/itemPreconfigured' => [
            'GET' => [
                'secure' => false,
                'service' => [
                    'class' => \Magento\TestModuleMSC\Api\AllSoapAndRestInterface::class,
                    'method' => 'getPreconfiguredItem',
                ],
                'resources' => [
                    'Magento_TestModuleMSC::resource1' => true,
                    'Magento_TestModuleMSC::resource2' => true,
                ],
                'parameters' => [],
            ]
        ]
    ],
];
