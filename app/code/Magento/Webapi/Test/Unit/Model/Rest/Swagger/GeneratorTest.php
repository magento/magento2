<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Rest\Swagger;

use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\CustomAttribute\ServiceTypeListInterface;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Magento\Store\Model\Store;
use Magento\Webapi\Model\Cache\Type\Webapi;
use Magento\Webapi\Model\Rest\Swagger;
use Magento\Webapi\Model\Rest\Swagger\Generator;
use Magento\Webapi\Model\Rest\SwaggerFactory;
use Magento\Webapi\Model\ServiceMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Webapi\Model\Rest\Swagger\Generator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorTest extends TestCase
{
    private const OPERATION_NAME = 'operationName';

    /**  @var Generator */
    protected $generator;

    /**  @var ServiceMetadata|MockObject */
    protected $serviceMetadataMock;

    /**  @var SwaggerFactory|MockObject */
    protected $swaggerFactoryMock;

    /** @var Webapi|MockObject */
    protected $cacheMock;

    /** @var TypeProcessor|MockObject */
    protected $typeProcessorMock;

    /**
     * @var CustomAttributeTypeLocatorInterface|MockObject
     */
    protected $customAttributeTypeLocatorMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    private $serializer;

    /**
     * @var ProductMetadata|MockObject
     */
    private $productMetadata;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->serviceMetadataMock = $this->getMockBuilder(
            ServiceMetadata::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);

        $swagger = $this->objectManager->getObject(Swagger::class);
        $this->swaggerFactoryMock = $this->getMockBuilder(
            SwaggerFactory::class
        )->onlyMethods(
            ['create']
        )->disableOriginalConstructor()
            ->getMock();
        $this->swaggerFactoryMock->expects($this->any())->method('create')->willReturn($swagger);

        $this->cacheMock = $this->getMockBuilder(Webapi::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock->expects($this->any())->method('load')->willReturn(false);
        $this->cacheMock->expects($this->any())->method('save')->willReturn(true);

        $this->typeProcessorMock = $this->getMockBuilder(TypeProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeProcessorMock->expects($this->any())
            ->method('getOperationName')
            ->willReturn(self::OPERATION_NAME);

        $this->customAttributeTypeLocatorMock = $this->getMockBuilder(
            ServiceTypeListInterface::class
        )->disableOriginalConstructor()
            ->onlyMethods(['getDataTypes'])
            ->getMockForAbstractClass();
        $this->customAttributeTypeLocatorMock->expects($this->any())
            ->method('getDataTypes')
            ->willReturn(['customAttributeClass']);

        $storeMock = $this->getMockBuilder(
            Store::class
        )->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->any())
            ->method('getCode')
            ->willReturn('store_code');

        /** @var Authorization|MockObject $authorizationMock */
        $authorizationMock = $this->getMockBuilder(Authorization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authorizationMock->expects($this->any())->method('isAllowed')->willReturn(true);

        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->productMetadata = $this->createMock(ProductMetadata::class);
        $objects = [
            [
                Json::class,
                $this->createMock(Json::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);
        $this->generator = $this->objectManager->getObject(
            Generator::class,
            [
                'swaggerFactory' => $this->swaggerFactoryMock,
                'cache' => $this->cacheMock,
                'typeProcessor' => $this->typeProcessorMock,
                'serviceMetadata' => $this->serviceMetadataMock,
                'serviceTypeList' => $this->customAttributeTypeLocatorMock,
                'authorization' => $authorizationMock,
                'serializer' => $this->serializer,
                'productMetadata' => $this->productMetadata
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
            ->willReturnMap($typeData);

        $this->typeProcessorMock->expects($this->any())
            ->method('isTypeSimple')
            ->willReturnMap(
                [
                    ['int', true],
                ]
            );

        $this->productMetadata->expects($this->once())
            ->method('getVersion')
            ->willReturn('UNKNOWN');

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
    public static function generateDataProvider()
    {
        return [
            [
                [
                    'methods' => [
                        'execute' => [
                            'method' => 'execute',
                            'inputRequired' => false,
                            'isSecure' => false,
                            'resources' => [
                                "anonymous"
                            ],
                            'methodAlias' => 'execute',
                            'parameters' => [],
                            'documentation' => 'Do Magic!',
                            'interface' => [
                                'in' => [
                                    'parameters' => [
                                        'searchRequest' => [
                                            'type' => 'DreamVendorDreamModuleApiDataSearchRequestInterface',
                                            'required' => true,
                                            'documentation' => ""
                                        ]
                                    ]
                                ],
                                'out' => [
                                    'parameters' => [
                                        'result' => [
                                            'type' => 'DreamVendorDreamModuleApiDataSearchResultInterface',
                                            'documentation' => null,
                                            'required' => true
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'class' => 'DreamVendor\DreamModule\Api\ExecuteStuff',
                    'description' => '',
                    'routes' => [
                        '/V1/dream-vendor/dream-module/execute-stuff' => [
                            'GET' => [
                                'method' => 'execute',
                                'parameters' => []
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'DreamVendorDreamModuleApiDataSearchRequestInterface',
                        [
                            'documentation' => '',
                            'parameters' => [
                                'stuff' => [
                                    'type' => 'DreamVendorDreamModuleApiDataStuffInterface',
                                    'required' => true,
                                    'documentation' => 'Empty Extension Point'
                                ]
                            ]
                        ]
                    ],
                    [
                        'DreamVendorDreamModuleApiDataSearchResultInterface',
                        [
                            'documentation' => '',
                            'parameters' => [
                                'totalCount' => [
                                    'type' => 'int',
                                    'required' => true,
                                    'documentation' => 'Processed count.'
                                ],
                                'stuff' => [
                                    'type' => 'DreamVendorDreamModuleApiDataStuffInterface',
                                    'required' => true,
                                    'documentation' => 'Empty Extension Point'
                                ]
                            ]
                        ]
                    ],
                    [
                        'DreamVendorDreamModuleApiDataStuffInterface',
                        [
                            'documentation' => '',
                            'parameters' => []
                        ]
                    ]
                ],
                // @codingStandardsIgnoreStart
                '{"securityDefinitions":{"api_key":{"type":"apiKey","name":"api_key","in":"header"}},"swagger":"2.0","info":{"version":"","title":""},"host":"magento.host","basePath":"/rest/default","schemes":["http://"],"tags":[{"name":"testModule5AllSoapAndRestV2","description":""}],"paths":{"/V1/dream-vendor/dream-module/execute-stuff":{"get":{"tags":["testModule5AllSoapAndRestV2"],"description":"Do Magic!","operationId":"GetV1DreamvendorDreammoduleExecutestuff","consumes":["application/json","application/xml"],"produces":["application/json","application/xml"],"responses":{"200":{"description":"200 Success.","schema":{"$ref":"#/definitions/dream-vendor-dream-module-api-data-search-result-interface"}},"default":{"description":"Unexpected error","schema":{"$ref":"#/definitions/error-response"}}}}}},"definitions":{"error-response":{"type":"object","properties":{"message":{"type":"string","description":"Error message"},"errors":{"$ref":"#/definitions/error-errors"},"code":{"type":"integer","description":"Error code"},"parameters":{"$ref":"#/definitions/error-parameters"},"trace":{"type":"string","description":"Stack trace"}},"required":["message"]},"error-errors":{"type":"array","description":"Errors list","items":{"$ref":"#/definitions/error-errors-item"}},"error-errors-item":{"type":"object","description":"Error details","properties":{"message":{"type":"string","description":"Error message"},"parameters":{"$ref":"#/definitions/error-parameters"}}},"error-parameters":{"type":"array","description":"Error parameters list","items":{"$ref":"#/definitions/error-parameters-item"}},"error-parameters-item":{"type":"object","description":"Error parameters item","properties":{"resources":{"type":"string","description":"ACL resource"},"fieldName":{"type":"string","description":"Missing or invalid field name"},"fieldValue":{"type":"string","description":"Incorrect field value"}}},"dream-vendor-dream-module-api-data-search-result-interface":{"type":"object","description":"","properties":{"total_count":{"type":"integer","description":"Processed count."},"stuff":{"$ref":"#/definitions/dream-vendor-dream-module-api-data-stuff-interface"}},"required":["total_count","stuff"]},"dream-vendor-dream-module-api-data-stuff-interface":{"type":"object","description":""}}}'
                // @codingStandardsIgnoreEnd
            ],
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
                                    'throws' => [LocalizedException::class],
                                ],
                            ],
                        ],
                    ],
                    'class' => 'Magento\TestModule5\Service\V2\AllSoapAndRestInterface',
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
                    [
                        'TestModule5V2EntityAllSoapAndRest',
                        [
                            'documentation' => 'Some Data Object',
                            'parameters' => [
                                'price' => [
                                    'type' => 'int',
                                    'required' => true,
                                    'documentation' => ""
                                ]
                            ]
                        ]
                    ]
                ],
                // @codingStandardsIgnoreStart
                '{"securityDefinitions":{"api_key":{"type":"apiKey","name":"api_key","in":"header"}},"swagger":"2.0","info":{"version":"","title":""},"host":"magento.host","basePath":"/rest/default","schemes":["http://"],"tags":[{"name":"testModule5AllSoapAndRestV2","description":"AllSoapAndRestInterface"}],"paths":{"/V1/testModule5":{"post":{"tags":["testModule5AllSoapAndRestV2"],"description":"Add new item.","operationId":"PostV1TestModule5","consumes":["application/json","application/xml"],"produces":["application/json","application/xml"],"parameters":[{"name":"PostV1TestModule5Body","in":"body","schema":{"required":["item"],"properties":{"item":{"$ref":"#/definitions/test-module5-v2-entity-all-soap-and-rest"}},"type":"object","xml":{"name":"request"}}}],"responses":{"200":{"description":"200 Success.","schema":{"$ref":"#/definitions/test-module5-v2-entity-all-soap-and-rest"}},"401":{"description":"401 Unauthorized","schema":{"$ref":"#/definitions/error-response"}},"500":{"description":"Internal Server error","schema":{"$ref":"#/definitions/error-response"}},"default":{"description":"Unexpected error","schema":{"$ref":"#/definitions/error-response"}}}}}},"definitions":{"error-response":{"type":"object","properties":{"message":{"type":"string","description":"Error message"},"errors":{"$ref":"#/definitions/error-errors"},"code":{"type":"integer","description":"Error code"},"parameters":{"$ref":"#/definitions/error-parameters"},"trace":{"type":"string","description":"Stack trace"}},"required":["message"]},"error-errors":{"type":"array","description":"Errors list","items":{"$ref":"#/definitions/error-errors-item"}},"error-errors-item":{"type":"object","description":"Error details","properties":{"message":{"type":"string","description":"Error message"},"parameters":{"$ref":"#/definitions/error-parameters"}}},"error-parameters":{"type":"array","description":"Error parameters list","items":{"$ref":"#/definitions/error-parameters-item"}},"error-parameters-item":{"type":"object","description":"Error parameters item","properties":{"resources":{"type":"string","description":"ACL resource"},"fieldName":{"type":"string","description":"Missing or invalid field name"},"fieldValue":{"type":"string","description":"Incorrect field value"}}},"test-module5-v2-entity-all-soap-and-rest":{"type":"object","description":"Some Data Object","properties":{"price":{"type":"integer"}},"required":["price"]}}}'
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
                                    'throws' => [LocalizedException::class],
                                ],
                            ],
                        ],
                    ],
                    'class' => 'Magento\TestModule5\Service\V2\AllSoapAndRestInterface',
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
                    [
                        'TestModule5V2EntityAllSoapAndRest',
                        [
                            'documentation' => 'Some Data Object',
                            'parameters' => [
                                'price' => [
                                    'type' => 'int',
                                    'required' => true,
                                    'documentation' => ""
                                ]
                            ]
                        ]
                    ]
                ],
                // @codingStandardsIgnoreStart
                '{"securityDefinitions":{"api_key":{"type":"apiKey","name":"api_key","in":"header"}},"swagger":"2.0","info":{"version":"","title":""},"host":"magento.host","basePath":"/rest/default","schemes":["http://"],"tags":[{"name":"testModule5AllSoapAndRestV2","description":"AllSoapAndRestInterface"}],"paths":{"/V1/testModule5":{"get":{"tags":["testModule5AllSoapAndRestV2"],"description":"Retrieve existing item.","operationId":"GetV1TestModule5","consumes":["application/json","application/xml"],"produces":["application/json","application/xml"],"responses":{"200":{"description":"200 Success.","schema":{"$ref":"#/definitions/test-module5-v2-entity-all-soap-and-rest"}},"401":{"description":"401 Unauthorized","schema":{"$ref":"#/definitions/error-response"}},"500":{"description":"Internal Server error","schema":{"$ref":"#/definitions/error-response"}},"default":{"description":"Unexpected error","schema":{"$ref":"#/definitions/error-response"}}}}}},"definitions":{"error-response":{"type":"object","properties":{"message":{"type":"string","description":"Error message"},"errors":{"$ref":"#/definitions/error-errors"},"code":{"type":"integer","description":"Error code"},"parameters":{"$ref":"#/definitions/error-parameters"},"trace":{"type":"string","description":"Stack trace"}},"required":["message"]},"error-errors":{"type":"array","description":"Errors list","items":{"$ref":"#/definitions/error-errors-item"}},"error-errors-item":{"type":"object","description":"Error details","properties":{"message":{"type":"string","description":"Error message"},"parameters":{"$ref":"#/definitions/error-parameters"}}},"error-parameters":{"type":"array","description":"Error parameters list","items":{"$ref":"#/definitions/error-parameters-item"}},"error-parameters-item":{"type":"object","description":"Error parameters item","properties":{"resources":{"type":"string","description":"ACL resource"},"fieldName":{"type":"string","description":"Missing or invalid field name"},"fieldValue":{"type":"string","description":"Incorrect field value"}}},"test-module5-v2-entity-all-soap-and-rest":{"type":"object","description":"Some Data Object","properties":{"price":{"type":"integer"}},"required":["price"]}}}'
                // @codingStandardsIgnoreEnd
            ],
        ];
    }

    /**
     * @param string $typeName
     * @param array $result
     * @dataProvider getObjectSchemaDataProvider
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

    /**
     * @return array
     */
    public static function getObjectSchemaDataProvider()
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
     * @dataProvider generateDefinitionDataProvider
     */
    public function testGenerateDefinition($typeData, $expected)
    {
        $getTypeData = function ($type) use ($typeData) {
            return $typeData[$type];
        };

        $this->typeProcessorMock
            ->method('getTypeData')
            ->willReturnCallback($getTypeData);

        $method = new \ReflectionMethod($this->generator, 'generateDefinition');
        $method->setAccessible(true);
        $actual = $method->invoke($this->generator, key($typeData));

        ksort($expected);
        ksort($actual);

        $this->assertSame(json_encode($expected), json_encode($actual));
    }

    /**
     * @return array
     */
    public static function generateDefinitionDataProvider()
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
