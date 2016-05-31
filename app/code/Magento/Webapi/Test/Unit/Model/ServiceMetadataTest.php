<?php
/**
 * ServiceMetadata Unit tests.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class implements tests for \Magento\Webapi\Model\ServiceMetadata class.
 */
namespace Magento\Webapi\Test\Unit\Model;

class ServiceMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Model\ServiceMetadata */
    protected $serviceMetadata;

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
            'Magento\Webapi\Model\Config\ClassReflector',
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
            'services' => [
                'Magento\Customer\Api\AccountManagementInterface' => [
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
                ],
                'Magento\Customer\Api\CustomerRepositoryInterface' => [
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
                            'class' => 'Magento\Customer\Api\AccountManagementInterface',
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
                            'class' => 'Magento\Customer\Api\CustomerRepositoryInterface',
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
        $cacheMock = $this->getMockBuilder('Magento\Webapi\Model\Cache\Type\Webapi')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \Magento\Webapi\Model\Config\Reader */
        $readerMock = $this->getMockBuilder('Magento\Webapi\Model\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $readerMock->expects($this->any())->method('read')->will($this->returnValue($servicesConfig));

        /** @var $config \Magento\Webapi\Model\Config */
        $config = new \Magento\Webapi\Model\Config($cacheMock, $readerMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $typeProcessor = $objectManager->getObject('Magento\Framework\Reflection\TypeProcessor');

        /** @var $config \Magento\Webapi\Model\ServiceMetadata */
        $this->serviceMetadata = new \Magento\Webapi\Model\ServiceMetadata(
            $config,
            $cacheMock,
            $classReflection,
            $typeProcessor
        );

        parent::setUp();
    }

    /**
     * Test identifying service name including subservices using class name.
     *
     * @dataProvider serviceNameDataProvider
     */
    public function testGetServiceName($className, $version, $preserveVersion, $expected)
    {
        $actual = $this->serviceMetadata->getServiceName($className, $version, $preserveVersion);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Dataprovider for testGetServiceName
     *
     * @return string
     */
    public function serviceNameDataProvider()
    {
        return [
            ['Magento\Customer\Api\AccountManagementInterface', 'V1', false, 'customerAccountManagement'],
            ['Magento\Customer\Api\AddressRepositoryInterface', 'V1', true, 'customerAddressRepositoryV1'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider dataProviderForTestGetServiceNameInvalidName
     */
    public function testGetServiceNameInvalidName($interfaceClassName, $version)
    {
        $this->serviceMetadata->getServiceName($interfaceClassName, $version);
    }

    /**
     * Dataprovider for testGetServiceNameInvalidName
     *
     * @return string
     */
    public function dataProviderForTestGetServiceNameInvalidName()
    {
        return [
            ['BarV1Interface', 'V1'], // Missed vendor, module, 'Service'
            ['Service\\V1Interface', 'V1'], // Missed vendor and module
            ['Magento\\Foo\\Service\\BarVxInterface', 'V1'], // Version number should be a number
            ['Magento\\Foo\\Service\\BarInterface', 'V1'], // Version missed
            ['Magento\\Foo\\Service\\BarV1', 'V1'], // 'Interface' missed
            ['Foo\\Service\\BarV1Interface', 'V1'], // Module missed
            ['Foo\\BarV1Interface', 'V1'] // Module and 'Service' missed
        ];
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
            'class' => 'Magento\Customer\Api\AccountManagementInterface',
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
            'class' => 'Magento\Customer\Api\AccountManagementInterface',
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

require_once realpath(__DIR__ . '/../_files/test_interfaces.php');
