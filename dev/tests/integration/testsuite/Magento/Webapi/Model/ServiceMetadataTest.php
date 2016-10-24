<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model;

class ServiceMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Model\ServiceMetadata
     */
    private $serviceMetadata;

    /**
     * Set up helper.
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $interfaceParameters = [
            'activateById' => [
                'interface' => [
                    'in' => [
                        'parameters' => [
                            'customerId' => [
                                'force' => true,
                                'value' => '%customer_id%',
                            ],
                            'requiredInputParameter' => [
                                'required' => true,
                            ],
                        ],
                    ],
                    'out' => [
                        'parameters' => [
                            'outputParameter' => [
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $classReflection = $this->getMock(
            \Magento\Webapi\Model\Config\ClassReflector::class,
            ['reflectClassMethods', 'extractClassDescription'],
            [],
            '',
            false
        );
        $classReflection->expects($this->any())
            ->method('reflectClassMethods')
            ->will($this->returnValue($interfaceParameters));
        $classReflection->expects($this->any())
            ->method('extractClassDescription')
            ->will($this->returnValue('classDescription'));

        $servicesConfig = [
            'services' => [\Magento\Customer\Api\AccountManagementInterface::class => [
                    'V1' => [
                        'methods' => [
                            'activateById' => [
                                'resources' => [
                                    [
                                        'Magento_Customer::manage',
                                    ],
                                ],
                                'secure' => false,
                            ],
                        ],
                    ],
                ], \Magento\Customer\Api\CustomerRepositoryInterface::class => [
                    'V1' => [
                        'methods' => [
                            'getById' => [
                                'resources' => [
                                    [
                                        'Magento_Customer::customer',
                                    ],
                                ],
                                'secure' => false,
                            ],
                        ],
                    ],
                ],
            ],
            'routes' => [
                '/V1/customers/me/activate' => [
                    'PUT' => [
                        'secure' => false,
                        'service' => [
                            'class' => \Magento\Customer\Api\AccountManagementInterface::class,
                            'method' => 'activateById',
                        ],
                        'resources' => [
                            'self' => true,
                        ],
                        'parameters' => [
                            'customerId' => [
                                'force' => true,
                                'value' => '%customer_id%',
                            ],
                        ],
                    ],
                ],
                '/V1/customers/:customerId' => [
                    'GET' => [
                        'secure' => false,
                        'service' => [
                            'class' => \Magento\Customer\Api\CustomerRepositoryInterface::class,
                            'method' => 'getById',
                        ],
                        'resources' => [
                            'Magento_Customer::customer' => true,
                        ],
                        'parameters' => [
                        ],
                    ],
                ],
            ]
        ];

        /**
         * @var $cacheMock \Magento\Webapi\Model\Cache\Type\Webapi
         */
        $cacheMock = $this->getMockBuilder(\Magento\Webapi\Model\Cache\Type\Webapi::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \Magento\Webapi\Model\Config\Reader */
        $readerMock = $this->getMockBuilder(\Magento\Webapi\Model\Config\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $readerMock->expects($this->any())->method('read')->will($this->returnValue($servicesConfig));

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $config \Magento\Webapi\Model\Config */
        $config = $objectManager->create(
            \Magento\Webapi\Model\Config::class,
            [
                'cache' => $cacheMock,
                'configReader' => $readerMock,
            ]
        );

        $typeProcessor = $objectManager->create(\Magento\Framework\Reflection\TypeProcessor::class);

        /** @var $config \Magento\Webapi\Model\ServiceMetadata */
        $this->serviceMetadata = $objectManager->create(
            \Magento\Webapi\Model\ServiceMetadata::class,
            [
                'config' => $config,
                'cache' => $cacheMock,
                'classReflector' => $classReflection,
                'typeProcessor' => $typeProcessor,
            ]
        );
    }

    public function testGetServiceMetadata()
    {
        $expectedResult = [
            'methods' => [
                'activateById' => [
                    'method' => 'activateById',
                    'inputRequired' => '',
                    'isSecure' => '',
                    'resources' => [['Magento_Customer::manage']],
                    'interface' => [
                        'in' => [
                            'parameters' => [
                                'customerId' => [
                                    'force' => true,
                                    'value' => '%customer_id%',
                                ],
                                'requiredInputParameter' => [
                                    'required' => true,
                                ],
                            ],
                        ],
                        'out' => [
                            'parameters' => [
                                'outputParameter' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'class' => \Magento\Customer\Api\AccountManagementInterface::class,
            'description' => 'classDescription',
        ];
        $result = $this->serviceMetadata->getServiceMetadata('customerAccountManagementV1');
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetRouteMetadata()
    {
        $expectedResult = [
            'methods' => [
                'activateById' => [
                    'method' => 'activateById',
                    'inputRequired' => '',
                    'isSecure' => '',
                    'resources' => [['Magento_Customer::manage']],
                    'interface' => [
                        'in' => [
                            'parameters' => [
                                'customerId' => [
                                    'force' => true,
                                    'value' => '%customer_id%',
                                ],
                                'requiredInputParameter' => [
                                    'required' => true,
                                ],
                            ],
                        ],
                        'out' => [
                            'parameters' => [
                                'outputParameter' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'class' => \Magento\Customer\Api\AccountManagementInterface::class,
            'description' => 'classDescription',
            'routes' => [
                '/V1/customers/me/activate' => [
                    'PUT' => [
                        'method' => 'activateById',
                        'parameters' => [
                            'customerId' => [
                                'force' => true,
                                'value' => '%customer_id%'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $result = $this->serviceMetadata->getRouteMetadata('customerAccountManagementV1');
        $this->assertEquals($expectedResult, $result);
    }
}
