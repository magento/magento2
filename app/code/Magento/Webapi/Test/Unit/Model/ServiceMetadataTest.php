<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Webapi\Model\Config;
use Magento\Webapi\Model\Cache\Type\Webapi;
use Magento\Webapi\Model\Config\ClassReflector;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Webapi\Model\ServiceMetadata;
use Magento\Customer\Api\CustomerRepositoryInterface;

class ServiceMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceMetadata
     */
    private $serviceMetadata;

    /**
     * @var Webapi|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ClassReflector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $classReflectorMock;

    /**
     * @var TypeProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeProcessorMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configMock = $this->getMock(Config::class, [], [], '', false);
        $this->cacheMock = $this->getMock(Webapi::class, [], [], '', false);
        $this->classReflectorMock = $this->getMock(ClassReflector::class, [], [], '', false);
        $this->typeProcessorMock = $this->getMock(TypeProcessor::class, [], [], '', false);
        $this->serializerMock = $this->getMock(SerializerInterface::class);

        $this->serviceMetadata = $objectManager->getObject(
            ServiceMetadata::class,
            [
                'config' => $this->configMock,
                'cache' => $this->cacheMock,
                'classReflector' => $this->classReflectorMock,
                'typeProcessor' => $this->typeProcessorMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetServicesConfig()
    {
        $servicesConfig = ['foo' => 'bar'];
        $typeData = ['bar' => 'foo'];
        $serializedServicesConfig = 'serialized services config';
        $serializedTypeData = 'serialized type data';
        $this->cacheMock->expects($this->at(0))
            ->method('load')
            ->with(ServiceMetadata::SERVICES_CONFIG_CACHE_ID)
            ->willReturn($serializedServicesConfig);
        $this->cacheMock->expects($this->at(1))
            ->method('load')
            ->with(ServiceMetadata::REFLECTED_TYPES_CACHE_ID)
            ->willReturn($serializedTypeData);
        $this->serializerMock->expects($this->at(0))
            ->method('unserialize')
            ->with($serializedServicesConfig)
            ->willReturn($servicesConfig);
        $this->serializerMock->expects($this->at(1))
            ->method('unserialize')
            ->with($serializedTypeData)
            ->willReturn($typeData);
        $this->typeProcessorMock->expects($this->once())
            ->method('setTypesData')
            ->with($typeData);
        $this->serviceMetadata->getServicesConfig();
        $this->assertEquals($servicesConfig, $this->serviceMetadata->getServicesConfig());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetServicesConfigNoCache()
    {
        $servicesConfig = [
            'services' => [
                CustomerRepositoryInterface::class => [
                    'V1' => [
                        'methods' => [
                            'getById' => [
                                'resources' => [
                                    [
                                        'Magento_Customer::customer',
                                    ]
                                ],
                                'secure' => false
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $methodsReflectionData = [
            'getById' => [
                'documentation' => 'Get customer by customer ID.',
                'interface' => [
                    'in' => [
                        'parameters' => [
                            'customerId' => [
                                'type' => 'int',
                                'required' => true,
                                'documentation' => null
                            ]
                        ]
                    ],
                    'out' => [
                        'parameters' => [
                            'result' => [
                                'type' => 'CustomerDataCustomerInterface',
                                'required' => true,
                                'documentation' => null
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $servicesMetadata = [
            'customerCustomerRepositoryV1' => [
                'methods' => array_merge_recursive(
                    [
                        'getById' => [
                            'resources' => [
                                [
                                    'Magento_Customer::customer',
                                ],
                            ],
                            'method' => 'getById',
                            'inputRequired' => false,
                            'isSecure' => false,
                        ]
                    ],
                    $methodsReflectionData
                ),
                'class' => CustomerRepositoryInterface::class,
                'description' => 'Customer CRUD interface.'
            ]
        ];
        $typeData = [
            'CustomerDataCustomerInterface' => [
                'documentation' => 'Customer interface.',
                'parameters' => [
                    'id' => [
                        'type' => 'int',
                        'required' => false,
                        'documentation' => 'Customer id'
                    ]
                ]
            ]
        ];
        $serializedServicesConfig = 'serialized services config';
        $serializedTypeData = 'serialized type data';
        $this->cacheMock->expects($this->at(0))
            ->method('load')
            ->with(ServiceMetadata::SERVICES_CONFIG_CACHE_ID)
            ->willReturn(false);
        $this->cacheMock->expects($this->at(1))
            ->method('load')
            ->with(ServiceMetadata::REFLECTED_TYPES_CACHE_ID)
            ->willReturn(false);
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->configMock->expects($this->once())
            ->method('getServices')
            ->willReturn($servicesConfig);
        $this->classReflectorMock->expects($this->once())
            ->method('reflectClassMethods')
            ->willReturn($methodsReflectionData);
        $this->classReflectorMock->expects($this->once())
            ->method('extractClassDescription')
            ->with(CustomerRepositoryInterface::class)
            ->willReturn('Customer CRUD interface.');
        $this->typeProcessorMock->expects($this->once())
            ->method('getTypesData')
            ->willReturn($typeData);
        $this->serializerMock->expects($this->at(0))
            ->method('serialize')
            ->with($servicesMetadata)
            ->willReturn($serializedServicesConfig);
        $this->serializerMock->expects($this->at(1))
            ->method('serialize')
            ->with($typeData)
            ->willReturn($serializedTypeData);
        $this->cacheMock->expects($this->at(2))
            ->method('save')
            ->with(
                $serializedServicesConfig,
                ServiceMetadata::SERVICES_CONFIG_CACHE_ID
            );
        $this->cacheMock->expects($this->at(3))
            ->method('save')
            ->with(
                $serializedTypeData,
                ServiceMetadata::REFLECTED_TYPES_CACHE_ID
            );
        $this->serviceMetadata->getServicesConfig();
        $this->assertEquals($servicesMetadata, $this->serviceMetadata->getServicesConfig());
    }

    public function testGetRoutesConfig()
    {
        $routesConfig = ['foo' => 'bar'];
        $typeData = ['bar' => 'foo'];
        $serializedRoutesConfig = 'serialized routes config';
        $serializedTypeData = 'serialized type data';
        $this->cacheMock->expects($this->at(0))
            ->method('load')
            ->with(ServiceMetadata::ROUTES_CONFIG_CACHE_ID)
            ->willReturn($serializedRoutesConfig);
        $this->cacheMock->expects($this->at(1))
            ->method('load')
            ->with(ServiceMetadata::REFLECTED_TYPES_CACHE_ID)
            ->willReturn($serializedTypeData);
        $this->serializerMock->expects($this->at(0))
            ->method('unserialize')
            ->with($serializedRoutesConfig)
            ->willReturn($routesConfig);
        $this->serializerMock->expects($this->at(1))
            ->method('unserialize')
            ->with($serializedTypeData)
            ->willReturn($typeData);
        $this->typeProcessorMock->expects($this->once())
            ->method('setTypesData')
            ->with($typeData);
        $this->serviceMetadata->getRoutesConfig();
        $this->assertEquals($routesConfig, $this->serviceMetadata->getRoutesConfig());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetRoutesConfigNoCache()
    {
        $servicesConfig = [
            'services' => [
                CustomerRepositoryInterface::class => [
                    'V1' => [
                        'methods' => [
                            'getById' => [
                                'resources' => [
                                    [
                                        'Magento_Customer::customer',
                                    ]
                                ],
                                'secure' => false
                            ]
                        ]
                    ]
                ]
            ],
            'routes' => [
                '/V1/customers/:customerId' => [
                    'GET' => [
                        'secure' => false,
                        'service' => [
                            'class' => CustomerRepositoryInterface::class,
                            'method' => 'getById'
                        ],
                        'resources' => [
                            'Magento_Customer::customer' => true
                        ],
                        'parameters' => []
                    ]
                ]
            ],
            'class' => CustomerRepositoryInterface::class,
            'description' => 'Customer CRUD interface.',
        ];
        $methodsReflectionData = [
            'getById' => [
                'documentation' => 'Get customer by customer ID.',
                'interface' => [
                    'in' => [
                        'parameters' => [
                            'customerId' => [
                                'type' => 'int',
                                'required' => true,
                                'documentation' => null
                            ]
                        ]
                    ],
                    'out' => [
                        'parameters' => [
                            'result' => [
                                'type' => 'CustomerDataCustomerInterface',
                                'required' => true,
                                'documentation' => null
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $routesMetadata = [
            'customerCustomerRepositoryV1' => [
                'methods' => array_merge_recursive(
                    [
                        'getById' => [
                            'resources' => [
                                [
                                    'Magento_Customer::customer',
                                ]
                            ],
                            'method' => 'getById',
                            'inputRequired' => false,
                            'isSecure' => false,
                        ]
                    ],
                    $methodsReflectionData
                ),
                'routes' => [
                    '/V1/customers/:customerId' => [
                        'GET' => [
                            'method' => 'getById',
                            'parameters' => []
                        ]
                    ]
                ],
                'class' => CustomerRepositoryInterface::class,
                'description' => 'Customer CRUD interface.'
            ]
        ];
        $typeData = [
            'CustomerDataCustomerInterface' => [
                'documentation' => 'Customer interface.',
                'parameters' => [
                    'id' => [
                        'type' => 'int',
                        'required' => false,
                        'documentation' => 'Customer id'
                    ]
                ]
            ]
        ];
        $serializedRoutesConfig = 'serialized routes config';
        $serializedTypeData = 'serialized type data';
        $this->cacheMock->expects($this->at(0))
            ->method('load')
            ->with(ServiceMetadata::ROUTES_CONFIG_CACHE_ID)
            ->willReturn(false);
        $this->cacheMock->expects($this->at(1))
            ->method('load')
            ->with(ServiceMetadata::REFLECTED_TYPES_CACHE_ID)
            ->willReturn(false);
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->configMock->expects($this->exactly(2))
            ->method('getServices')
            ->willReturn($servicesConfig);
        $this->classReflectorMock->expects($this->once())
            ->method('reflectClassMethods')
            ->willReturn($methodsReflectionData);
        $this->classReflectorMock->expects($this->once())
            ->method('extractClassDescription')
            ->with(CustomerRepositoryInterface::class)
            ->willReturn('Customer CRUD interface.');
        $this->typeProcessorMock->expects($this->exactly(2))
            ->method('getTypesData')
            ->willReturn($typeData);
        $this->serializerMock->expects($this->at(2))
            ->method('serialize')
            ->with($routesMetadata)
            ->willReturn($serializedRoutesConfig);
        $this->serializerMock->expects($this->at(3))
            ->method('serialize')
            ->with($typeData)
            ->willReturn($serializedTypeData);
        $this->cacheMock->expects($this->at(6))
            ->method('save')
            ->with(
                $serializedRoutesConfig,
                ServiceMetadata::ROUTES_CONFIG_CACHE_ID
            );
        $this->cacheMock->expects($this->at(7))
            ->method('save')
            ->with(
                $serializedTypeData,
                ServiceMetadata::REFLECTED_TYPES_CACHE_ID
            );
        $this->serviceMetadata->getRoutesConfig();
        $this->assertEquals($routesMetadata, $this->serviceMetadata->getRoutesConfig());
    }

    /**
     * @dataProvider getServiceNameDataProvider
     */
    public function testGetServiceName($className, $version, $preserveVersion, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->serviceMetadata->getServiceName($className, $version, $preserveVersion)
        );
    }

    /**
     * @return string
     */
    public function getServiceNameDataProvider()
    {
        return [
            [
                \Magento\Customer\Api\AccountManagementInterface::class,
                'V1',
                false,
                'customerAccountManagement'
            ],
            [
                \Magento\Customer\Api\AddressRepositoryInterface::class,
                'V1',
                true,
                'customerAddressRepositoryV1'
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getServiceNameInvalidNameDataProvider
     */
    public function testGetServiceNameInvalidName($interfaceClassName, $version)
    {
        $this->serviceMetadata->getServiceName($interfaceClassName, $version);
    }

    /**
     * @return string
     */
    public function getServiceNameInvalidNameDataProvider()
    {
        return [
            ['BarV1Interface', 'V1'], // Missed vendor, module and Service
            ['Service\\V1Interface', 'V1'], // Missed vendor and module
            ['Magento\\Foo\\Service\\BarVxInterface', 'V1'], // Version number should be a number
            ['Magento\\Foo\\Service\\BarInterface', 'V1'], // Missed version
            ['Magento\\Foo\\Service\\BarV1', 'V1'], // Missed Interface
            ['Foo\\Service\\BarV1Interface', 'V1'], // Missed module
            ['Foo\\BarV1Interface', 'V1'] // Missed module and Service
        ];
    }
}
