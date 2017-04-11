<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Rest\Swagger;

/**
 * Tests for \Magento\Webapi\Model\Rest\Swagger\Generator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    const OPERATION_NAME = 'operationName';

    /**  @var \Magento\Webapi\Model\Rest\Swagger\Generator */
    protected $generator;

    /**  @var \Magento\Webapi\Model\ServiceMetadata|\PHPUnit_Framework_MockObject_MockObject */
    protected $serviceMetadataMock;

    /**  @var \Magento\Webapi\Model\Rest\SwaggerFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $swaggerFactoryMock;

    /** @var \Magento\Webapi\Model\Cache\Type\Webapi|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheMock;

    /** @var \Magento\Framework\Reflection\TypeProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $typeProcessorMock;

    /**
     * @var \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customAttributeTypeLocatorMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    protected function setUp()
    {
        $this->serviceMetadataMock = $this->getMockBuilder(
            \Magento\Webapi\Model\ServiceMetadata::class
        )->disableOriginalConstructor()->getMock();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $swagger = $this->objectManager->getObject(\Magento\Webapi\Model\Rest\Swagger::class);
        $this->swaggerFactoryMock = $this->getMockBuilder(
            \Magento\Webapi\Model\Rest\SwaggerFactory::class
        )->setMethods(
            ['create']
        )->disableOriginalConstructor()->getMock();
        $this->swaggerFactoryMock->expects($this->any())->method('create')->will($this->returnValue($swagger));

        $this->cacheMock = $this->getMockBuilder(\Magento\Webapi\Model\Cache\Type\Webapi::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock->expects($this->any())->method('load')->will($this->returnValue(false));
        $this->cacheMock->expects($this->any())->method('save')->will($this->returnValue(true));

        $this->typeProcessorMock = $this->getMockBuilder(\Magento\Framework\Reflection\TypeProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeProcessorMock->expects($this->any())
            ->method('getOperationName')
            ->will($this->returnValue(self::OPERATION_NAME));

        $this->customAttributeTypeLocatorMock = $this->getMockBuilder(
            \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->customAttributeTypeLocatorMock->expects($this->any())
            ->method('getAllServiceDataInterfaces')
            ->willReturn(['$customAttributeClass']);

        $storeMock = $this->getMockBuilder(
            \Magento\Store\Model\Store::class
        )->disableOriginalConstructor()->getMock();

        $storeMock->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('store_code'));

        /** @var \Magento\Framework\Webapi\Authorization|\PHPUnit_Framework_MockObject_MockObject $authorizationMock */
        $authorizationMock = $this->getMockBuilder(\Magento\Framework\Webapi\Authorization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authorizationMock->expects($this->any())->method('isAllowed')->willReturn(true);

        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $this->generator = $this->objectManager->getObject(
            \Magento\Webapi\Model\Rest\Swagger\Generator::class,
            [
                'swaggerFactory' => $this->swaggerFactoryMock,
                'cache' => $this->cacheMock,
                'typeProcessor' => $this->typeProcessorMock,
                'serviceMetadata' => $this->serviceMetadataMock,
                'customAttributeTypeLocator' => $this->customAttributeTypeLocatorMock,
                'authorization' => $authorizationMock,
                'serializer' => $this->serializer
            ]
        );
    }

    /**
     * @covers \Magento\Webapi\Model\AbstractSchemaGenerator::generate()
     * @param string[] $serviceMetadata
     * @param string[] $typeData
     * @param string $schema
     * @dataProvider generateDataProvider
     */
    public function testGenerate($serviceMetadata, $typeData, $schema)
    {
        $service = 'testModule5AllSoapAndRestV2';
        $requestedService = [$service];

        $this->serviceMetadataMock->expects($this->any())
            ->method('getRouteMetadata')
            ->willReturn($serviceMetadata);
        $this->typeProcessorMock->expects($this->any())
            ->method('getTypeData')
            ->willReturnMap(
                [
                    ['TestModule5V2EntityAllSoapAndRest', $typeData],
                ]
            );

        $this->typeProcessorMock->expects($this->any())
            ->method('isTypeSimple')
            ->willReturnMap(
                [
                    ['int', true],
                ]
            );

        $this->assertEquals(
            $schema,
            $this->generator->generate(
                $requestedService,
                'http://',
                'magento.host',
                '/rest/default/schema?services=service1'
            )
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function generateDataProvider()
    {
        return [
            [
                [
                    'methods' => [
                        'create' => [
                            'method' => 'create',
                            'inputRequired' => false,
                            'isSecure' => false,
                            'resources' => ['Magento_TestModule5::resource3'],
                            'documentation' => 'Add new item.',
                            'interface' => [
                                'in' => [
                                    'parameters' => [
                                        'item' => [
                                            'type' => 'TestModule5V2EntityAllSoapAndRest',
                                            'documentation' => null,
                                            'required' => true,
                                        ],
                                    ],
                                ],
                                'out' => [
                                    'parameters' => [
                                        'result' => [
                                            'type' => 'TestModule5V2EntityAllSoapAndRest',
                                            'documentation' => null,
                                            'required' => true,
                                        ],
                                    ],
                                    'throws' => [\Magento\Framework\Exception\LocalizedException::class],
                                ],
                            ],
                        ],
                    ],
                    'class' => \Magento\TestModule5\Service\V2\AllSoapAndRestInterface::class,
                    'description' => 'AllSoapAndRestInterface',
                    'routes' => [
                        '/V1/testModule5' => [
                            'POST' => [
                                'method' => 'create',
                                'parameters' => [],
                            ],
                        ],
                    ],
                ],
                [
                    'documentation' => 'Some Data Object',
                    'parameters' => [
                        'price' => [
                            'type' => 'int',
                            'required' => true,
                            'documentation' => ""
                        ]
                    ]
                ],
                // @codingStandardsIgnoreStart
                '{"swagger":"2.0","info":{"version":"","title":""},"host":"magento.host","basePath":"/rest/default","schemes":["http://"],"tags":[{"name":"testModule5AllSoapAndRestV2","description":"AllSoapAndRestInterface"}],"paths":{"/V1/testModule5":{"post":{"tags":["testModule5AllSoapAndRestV2"],"description":"Add new item.","operationId":"' . self::OPERATION_NAME . 'Post","parameters":[{"name":"$body","in":"body","schema":{"required":["item"],"properties":{"item":{"$ref":"#/definitions/test-module5-v2-entity-all-soap-and-rest"}},"type":"object"}}],"responses":{"200":{"description":"200 Success.","schema":{"$ref":"#/definitions/test-module5-v2-entity-all-soap-and-rest"}},"401":{"description":"401 Unauthorized","schema":{"$ref":"#/definitions/error-response"}},"500":{"description":"Internal Server error","schema":{"$ref":"#/definitions/error-response"}},"default":{"description":"Unexpected error","schema":{"$ref":"#/definitions/error-response"}}}}}},"definitions":{"error-response":{"type":"object","properties":{"message":{"type":"string","description":"Error message"},"errors":{"$ref":"#/definitions/error-errors"},"code":{"type":"integer","description":"Error code"},"parameters":{"$ref":"#/definitions/error-parameters"},"trace":{"type":"string","description":"Stack trace"}},"required":["message"]},"error-errors":{"type":"array","description":"Errors list","items":{"$ref":"#/definitions/error-errors-item"}},"error-errors-item":{"type":"object","description":"Error details","properties":{"message":{"type":"string","description":"Error message"},"parameters":{"$ref":"#/definitions/error-parameters"}}},"error-parameters":{"type":"array","description":"Error parameters list","items":{"$ref":"#/definitions/error-parameters-item"}},"error-parameters-item":{"type":"object","description":"Error parameters item","properties":{"resources":{"type":"string","description":"ACL resource"},"fieldName":{"type":"string","description":"Missing or invalid field name"},"fieldValue":{"type":"string","description":"Incorrect field value"}}},"test-module5-v2-entity-all-soap-and-rest":{"type":"object","description":"Some Data Object","properties":{"price":{"type":"integer"}},"required":["price"]}}}'
                // @codingStandardsIgnoreEnd
            ],
            [
                [
                    'methods' => [
                        'items' => [
                            'method' => 'items',
                            'inputRequired' => false,
                            'isSecure' => false,
                            'resources' => ['Magento_TestModule5::resource1'],
                            'documentation' => 'Retrieve existing item.',
                            'interface' => [
                                'out' => [
                                    'parameters' => [
                                        'result' => [
                                            'type' => 'TestModule5V2EntityAllSoapAndRest',
                                            'documentation' => "",
                                            'required' => true,
                                        ],
                                    ],
                                    'throws' => [\Magento\Framework\Exception\LocalizedException::class],
                                ],
                            ],
                        ],
                    ],
                    'class' => \Magento\TestModule5\Service\V2\AllSoapAndRestInterface::class,
                    'description' => 'AllSoapAndRestInterface',
                    'routes' => [
                        '/V1/testModule5' => [
                            'GET' => [
                                'method' => 'items',
                                'parameters' => [],
                            ],
                        ],
                    ],
                ],
                [
                    'documentation' => 'Some Data Object',
                    'parameters' => [
                        'price' => [
                            'type' => 'int',
                            'required' => true,
                            'documentation' => ""
                        ]
                    ]
                ],
                // @codingStandardsIgnoreStart
                '{"swagger":"2.0","info":{"version":"","title":""},"host":"magento.host","basePath":"/rest/default","schemes":["http://"],"tags":[{"name":"testModule5AllSoapAndRestV2","description":"AllSoapAndRestInterface"}],"paths":{"/V1/testModule5":{"get":{"tags":["testModule5AllSoapAndRestV2"],"description":"Retrieve existing item.","operationId":"' . self::OPERATION_NAME . 'Get","responses":{"200":{"description":"200 Success.","schema":{"$ref":"#/definitions/test-module5-v2-entity-all-soap-and-rest"}},"401":{"description":"401 Unauthorized","schema":{"$ref":"#/definitions/error-response"}},"500":{"description":"Internal Server error","schema":{"$ref":"#/definitions/error-response"}},"default":{"description":"Unexpected error","schema":{"$ref":"#/definitions/error-response"}}}}}},"definitions":{"error-response":{"type":"object","properties":{"message":{"type":"string","description":"Error message"},"errors":{"$ref":"#/definitions/error-errors"},"code":{"type":"integer","description":"Error code"},"parameters":{"$ref":"#/definitions/error-parameters"},"trace":{"type":"string","description":"Stack trace"}},"required":["message"]},"error-errors":{"type":"array","description":"Errors list","items":{"$ref":"#/definitions/error-errors-item"}},"error-errors-item":{"type":"object","description":"Error details","properties":{"message":{"type":"string","description":"Error message"},"parameters":{"$ref":"#/definitions/error-parameters"}}},"error-parameters":{"type":"array","description":"Error parameters list","items":{"$ref":"#/definitions/error-parameters-item"}},"error-parameters-item":{"type":"object","description":"Error parameters item","properties":{"resources":{"type":"string","description":"ACL resource"},"fieldName":{"type":"string","description":"Missing or invalid field name"},"fieldValue":{"type":"string","description":"Incorrect field value"}}},"test-module5-v2-entity-all-soap-and-rest":{"type":"object","description":"Some Data Object","properties":{"price":{"type":"integer"}},"required":["price"]}}}'
                // @codingStandardsIgnoreEnd
            ],
        ];
    }

    /**
     * @param string $typeName
     * @param array $result
     * @dataProvider testGetObjectSchemaDataProvider
     */
    public function testGetObjectSchema($typeName, $description, $result)
    {
        $property = new \ReflectionProperty($this->generator, 'definitions');
        $property->setAccessible(true);
        $property->setValue($this->generator, ['customer-data-customer-interface' => []]);

        $method = new \ReflectionMethod($this->generator, 'getObjectSchema');
        $method->setAccessible(true);
        $actual = $method->invoke($this->generator, $typeName, $description);

        $this->assertSame(json_encode($result), json_encode($actual));
    }

    public function testGetObjectSchemaDataProvider()
    {
        return [
            [
                'string',
                '',
                ['type' => 'string']
            ],
            [
                'string[]',
                '',
                ['type' => 'array', 'items' => ['type' => 'string']]
            ],
            [
                'CustomerDataCustomerInterface',
                '',
                ['$ref' => '#/definitions/customer-data-customer-interface']
            ],
            [
                'CustomerDataCustomerInterface[]',
                '',
                ['type' => 'array', 'items' => ['$ref' => '#/definitions/customer-data-customer-interface']]
            ],
            [
                'CustomerDataCustomerInterface[]',
                'Customer interface',
                [
                    'type' => 'array',
                    'description' => 'Customer interface',
                    'items' => ['$ref' => '#/definitions/customer-data-customer-interface']],
            ]
        ];
    }

    /**
     * @param array $typeData
     * @param array $expected
     * @dataProvider testGenerateDefinitionDataProvider
     */
    public function testGenerateDefinition($typeData, $expected)
    {
        $getTypeData = function ($type) use ($typeData) {
            return $typeData[$type];
        };

        $this->typeProcessorMock
            ->method('getTypeData')
            ->will($this->returnCallback($getTypeData));

        $method = new \ReflectionMethod($this->generator, 'generateDefinition');
        $method->setAccessible(true);
        $actual = $method->invoke($this->generator, key($typeData));

        ksort($expected);
        ksort($actual);

        $this->assertSame(json_encode($expected), json_encode($actual));
    }

    public function testGenerateDefinitionDataProvider()
    {
        return [
            [
                [
                    'CustomerDataCustomerInterface' => [
                        'documentation' => 'Customer entity',
                        'parameters' => [
                            'id' => [
                                'type' => 'int',
                                'required' => false,
                                'documentation' => 'Customer id'
                            ],
                            'group_id' => [
                                'type' => 'int',
                                'required' => false,
                                'documentation' => 'Customer group ID'
                            ],
                            'email' => [
                                'type' => 'string',
                                'required' => false,
                                'documentation' => 'Customer email'
                            ],
                            'addresses' => [
                                'type' => 'CustomerDataAddressInterface[]',
                                'required' => false,
                                'documentation' => 'Customer addresses'
                            ]
                        ]
                    ],
                    'CustomerDataAddressInterface' => [
                        'documentation' => 'Customer entity',
                        'parameters' => [
                            'id' => [
                                'type' => 'int',
                                'required' => false,
                                'documentation' => 'Customer id'
                            ],
                            'group_id' => [
                                'type' => 'int',
                                'required' => false,
                                'documentation' => 'Customer group ID'
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'object',
                    'description' => 'Customer entity',
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                            'description' => 'Customer id'
                        ],
                        'group_id' => [
                            'type' => 'integer',
                            'description' => 'Customer group ID'
                        ],
                        'email' => [
                            'type' => 'string',
                            'description' => 'Customer email',
                        ],
                        'addresses' => [
                            'type' => 'array',
                            'description' => 'Customer addresses',
                            'items' => [
                                '$ref' => '#/definitions/customer-data-address-interface'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function testGetDefinitionReference()
    {
        $method = new \ReflectionMethod($this->generator, 'getDefinitionReference');
        $method->setAccessible(true);
        $actual = $method->invoke($this->generator, 'CustomerDataAddressInterface');

        $this->assertEquals('#/definitions/customer-data-address-interface', $actual);
    }
}
