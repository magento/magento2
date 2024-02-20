<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit;

use Magento\Eav\Model\TypeLocator;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use \Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Cache\Type\Reflection;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\FieldNamer;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\NameFinder;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Validator\IOLimit\DefaultPageSizeSetter;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Validator\IOLimit\IOLimitConfigProvider;
use Magento\Framework\Webapi\Validator\EntityArrayValidator;
use Magento\Framework\Webapi\ServiceTypeToEntityTypeMap;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\AssociativeArray;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\DataArray;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Nested;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\ObjectWithCustomAttributes;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\SimpleArray;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\SimpleConstructor;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService;
use Magento\Webapi\Test\Unit\Service\Entity\DataArrayData;
use Magento\Webapi\Test\Unit\Service\Entity\NestedData;
use Magento\Webapi\Test\Unit\Service\Entity\SimpleArrayData;
use Magento\Webapi\Test\Unit\Service\Entity\SimpleData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Webapi\Validator\EntityArrayValidator\InputArraySizeLimitValue;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceInputProcessorTest extends TestCase
{
    /** @var ServiceInputProcessor */
    protected $serviceInputProcessor;

    /** @var MockObject */
    protected $attributeValueFactoryMock;

    /** @var MockObject */
    protected $customAttributeTypeLocator;

    /** @var MockObject  */
    protected $objectManagerMock;

    /** @var  MethodsMap */
    protected $methodsMap;

    /** @var MockObject */
    protected $fieldNamer;

    /**
     * @var SearchCriteriaInterface|MockObject
     */
    private $searchCriteria;

    /**
     * @var ServiceTypeToEntityTypeMap|MockObject
     */
    private $serviceTypeToEntityTypeMap;

    /**
     * @var IOLimitConfigProvider|MockObject
     */
    private $inputLimitConfig;

    /**
     * @var DefaultPageSizeSetter|MockObject
     */
    private $defaultPageSizeSetter;

    /**
     * @var AddressInterface|MockObject
     */
    protected $addressMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->searchCriteria  = self::getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();
        $this->addressMock  = self::getMockBuilder(AddressInterface::class)
            ->getMock();
        $objectManagerStatic = [
            SearchCriteriaInterface::class => $this->searchCriteria,
            AddressInterface::class => $this->addressMock
        ];
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function ($className, $arguments = []) use ($objectManager, $objectManagerStatic) {
                    if (isset($objectManagerStatic[$className])) {
                        return $objectManagerStatic[$className];
                    }

                    return $objectManager->getObject($className, $arguments);
                }
            );

        /** @var TypeProcessor $typeProcessor */
        $typeProcessor = $objectManager->getObject(TypeProcessor::class);
        $cache = $this->getMockBuilder(Reflection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cache->expects($this->any())->method('load')->willReturn(false);

        $this->customAttributeTypeLocator = $this->getMockBuilder(
            TypeLocator::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\Api\AttributeDataBuilder */
        $this->attributeValueFactoryMock = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeValueFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () use ($objectManager) {
                    return $objectManager->getObject(AttributeValue::class);
                }
            );

        $this->fieldNamer = $this->getMockBuilder(FieldNamer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->methodsMap = $objectManager->getObject(
            MethodsMap::class,
            [
                'cache' => $cache,
                'typeProcessor' => $typeProcessor,
                'attributeTypeResolver' => $this->attributeValueFactoryMock->create(),
                'fieldNamer' => $this->fieldNamer
            ]
        );
        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->method('serialize')
            ->willReturn('serializedData');
        $serializerMock->method('unserialize')
            ->willReturn('unserializedData');
        $objectManager->setBackwardCompatibleProperty(
            $this->methodsMap,
            'serializer',
            $serializerMock
        );
        $this->serviceTypeToEntityTypeMap = $this->getMockBuilder(ServiceTypeToEntityTypeMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inputLimitConfig = self::getMockBuilder(IOLimitConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $inputArraySizeLimitValue = $this->createMock(InputArraySizeLimitValue::class);

        $this->defaultPageSizeSetter = self::getMockBuilder(DefaultPageSizeSetter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceInputProcessor = $objectManager->getObject(
            ServiceInputProcessor::class,
            [
                'typeProcessor' => $typeProcessor,
                'objectManager' => $this->objectManagerMock,
                'customAttributeTypeLocator' => $this->customAttributeTypeLocator,
                'attributeValueFactory' => $this->attributeValueFactoryMock,
                'methodsMap' => $this->methodsMap,
                'serviceTypeToEntityTypeMap' => $this->serviceTypeToEntityTypeMap,
                'serviceInputValidator' => new EntityArrayValidator(
                    50,
                    $this->inputLimitConfig,
                    $inputArraySizeLimitValue
                ),
                'defaultPageSizeSetter' => $this->defaultPageSizeSetter,
                'defaultPageSize' => 123
            ]
        );

        /** @var NameFinder $nameFinder */
        $nameFinder = $objectManager->getObject(NameFinder::class);
        $objectManager->setBackwardCompatibleProperty(
            $this->serviceInputProcessor,
            'nameFinder',
            $nameFinder
        );
    }

    public function testSimpleProperties()
    {
        $data = ['entityId' => 15, 'name' => 'Test'];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'simple',
            $data
        );
        $this->assertNotNull($result);
        $this->assertEquals(15, $result[0]);
        $this->assertEquals('Test', $result[1]);
    }

    public function testNonExistentPropertiesWithDefaultArgumentValue()
    {
        $data = [];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'simpleDefaultValue',
            $data
        );
        $this->assertNotNull($result);
        $this->assertEquals(TestService::DEFAULT_VALUE, $result[0]);
    }

    public function testNonExistentPropertiesWithoutDefaultArgumentValue()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('One or more input exceptions have occurred.');
        $data = [];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'simple',
            $data
        );
        $this->assertNull($result);
    }

    public function testNestedDataProperties()
    {
        $data = ['nested' => ['details' => ['entityId' => 15, 'name' => 'Test']]];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'nestedData',
            $data
        );
        $this->assertNotNull($result);
        $this->assertTrue($result[0] instanceof Nested);
        /** @var array $result */
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]);
        /** @var NestedData $arg */
        $arg = $result[0];
        $this->assertTrue($arg instanceof Nested);
        /** @var SimpleData $details */
        $details = $arg->getDetails();
        $this->assertNotNull($details);
        $this->assertTrue($details instanceof Simple);
        $this->assertEquals(15, $details->getEntityId());
        $this->assertEquals('Test', $details->getName());
    }

    public function testSimpleConstructorProperties()
    {
        $data = ['simpleConstructor' => ['entityId' => 15, 'name' => 'Test']];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'simpleConstructor',
            $data
        );
        $this->assertNotNull($result);
        $arg = $result[0];

        $this->assertTrue($arg instanceof SimpleConstructor);
        $this->assertEquals(15, $arg->getEntityId());
        $this->assertEquals('Test', $arg->getName());
    }

    public function testSimpleArrayProperties()
    {
        $data = ['ids' => [1, 2, 3, 4]];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'simpleArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertCount(1, $result);
        /** @var array $ids */
        $ids = $result[0];
        $this->assertNotNull($ids);
        $this->assertCount(4, $ids);
        $this->assertEquals($data['ids'], $ids);
    }

    public function testAssociativeArrayProperties()
    {
        $data = ['associativeArray' => ['key' => 'value', 'key_two' => 'value_two']];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertCount(1, $result);
        /** @var array $associativeArray */
        $associativeArray = $result[0];
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray['key']);
        $this->assertEquals('value_two', $associativeArray['key_two']);
    }

    public function testAssociativeArrayPropertiesWithItem()
    {
        $data = ['associativeArray' => ['item' => 'value']];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertCount(1, $result);
        /** @var array $associativeArray */
        $associativeArray = $result[0];
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray[0]);
    }

    public function testAssociativeArrayPropertiesWithItemArray()
    {
        $data = ['associativeArray' => ['item' => ['value1','value2']]];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertCount(1, $result);
        /** @var array $associativeArray */
        $array = $result[0];
        $this->assertNotNull($array);
        $this->assertEquals('value1', $array[0]);
        $this->assertEquals('value2', $array[1]);
    }

    public function testArrayOfDataObjectProperties()
    {
        $data = [
            'dataObjects' => [
                ['entityId' => 14, 'name' => 'First'],
                ['entityId' => 15, 'name' => 'Second'],
            ],
        ];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'dataArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertCount(1, $result);
        /** @var array $dataObjects */
        $dataObjects = $result[0];
        $this->assertCount(2, $dataObjects);
        /** @var SimpleData $first */
        $first = $dataObjects[0];
        /** @var SimpleData $second */
        $second = $dataObjects[1];
        $this->assertTrue($first instanceof Simple);
        $this->assertEquals(14, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertTrue($second instanceof Simple);
        $this->assertEquals(15, $second->getEntityId());
        $this->assertEquals('Second', $second->getName());
    }

    public function testArrayOfDataObjectPropertiesIsValidated()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage(
            'Maximum items of type "\\' . Simple::class . '" is 50'
        );
        $this->inputLimitConfig->method('isInputLimitingEnabled')
            ->willReturn(true);
        $objects = [];
        for ($i = 0; $i < 51; $i++) {
            $objects[] = ['entityId' => $i + 1, 'name' => 'Item' . $i];
        }
        $data = [
            'dataObjects' => $objects,
        ];
        $this->serviceInputProcessor->process(
            TestService::class,
            'dataArray',
            $data
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDefaultPageSizeSetterIsInvoked()
    {
        $this->defaultPageSizeSetter->expects(self::once())
            ->method('processSearchCriteria')
            ->with($this->searchCriteria);

        $data = [
            'searchCriteria' => []
        ];
        $this->serviceInputProcessor->process(
            TestService::class,
            'search',
            $data
        );
    }

    public function testNestedSimpleArrayProperties()
    {
        $data = ['arrayData' => ['ids' => [1, 2, 3, 4]]];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'nestedSimpleArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertCount(1, $result);
        /** @var SimpleArrayData $dataObject */
        $dataObject = $result[0];
        $this->assertTrue($dataObject instanceof SimpleArray);
        /** @var array $ids */
        $ids = $dataObject->getIds();
        $this->assertNotNull($ids);
        $this->assertCount(4, $ids);
        $this->assertEquals($data['arrayData']['ids'], $ids);
    }

    public function testNestedAssociativeArrayProperties()
    {
        $data = [
            'associativeArrayData' => ['associativeArray' => ['key' => 'value', 'key2' => 'value2']],
        ];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'nestedAssociativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertCount(1, $result);
        /** @var AssociativeArray $dataObject */
        $dataObject = $result[0];
        $this->assertTrue($dataObject instanceof AssociativeArray);
        /** @var array $associativeArray */
        $associativeArray = $dataObject->getAssociativeArray();
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray['key']);
        $this->assertEquals('value2', $associativeArray['key2']);
    }

    public function testNestedArrayOfDataObjectProperties()
    {
        $data = [
            'dataObjects' => [
                'items' => [['entityId' => 1, 'name' => 'First'], ['entityId' => 2, 'name' => 'Second']],
            ],
        ];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'nestedDataArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertCount(1, $result);
        /** @var \Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\DataArray $dataObjects */
        $dataObjects = $result[0];
        $this->assertTrue($dataObjects instanceof DataArray);
        /** @var array $items */
        $items = $dataObjects->getItems();
        $this->assertCount(2, $items);
        /** @var SimpleData $first */
        $first = $items[0];
        /** @var SimpleData $second */
        $second = $items[1];
        $this->assertTrue($first instanceof Simple);
        $this->assertEquals(1, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertTrue($second instanceof Simple);
        $this->assertEquals(2, $second->getEntityId());
        $this->assertEquals('Second', $second->getName());
    }

    /**
     * Covers object with custom attributes
     *
     * @dataProvider customAttributesDataProvider
     * @param $customAttributeType
     * @param $inputData
     * @param $expectedObject
     */
    public function testCustomAttributesProperties($customAttributeType, $inputData, $expectedObject)
    {
        $this->customAttributeTypeLocator->expects($this->any())->method('getType')->willReturn($customAttributeType);
        $this->serviceTypeToEntityTypeMap->expects($this->any())->method('getEntityType')->willReturn($expectedObject);

        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'ObjectWithCustomAttributesMethod',
            $inputData
        );

        $this->assertTrue($result[0] instanceof ObjectWithCustomAttributes);
        $this->assertEquals($expectedObject, $result[0]);
    }

    /**
     * Provides data for testCustomAttributesProperties
     *
     * @return array
     */
    public function customAttributesDataProvider()
    {
        return [
            'customAttributeInteger' => [
                'customAttributeType' => 'integer',
                'inputData' => [
                    'param' => [
                        'customAttributes' => [
                            [
                                'attribute_code' => TestService::CUSTOM_ATTRIBUTE_CODE,
                                'value' => TestService::DEFAULT_VALUE
                            ]
                        ]
                    ]
                ],
                'expectedObject'=>  $this->getObjectWithCustomAttributes('integer', TestService::DEFAULT_VALUE),
            ],
            'customAttributeIntegerCamelCaseCode' => [
                'customAttributeType' => 'integer',
                'inputData' => [
                    'param' => [
                        'customAttributes' => [
                            [
                                'attributeCode' => TestService::CUSTOM_ATTRIBUTE_CODE,
                                'value' => TestService::DEFAULT_VALUE
                            ]
                        ]
                    ]
                ],
                'expectedObject'=>  $this->getObjectWithCustomAttributes('integer', TestService::DEFAULT_VALUE),
            ],
            'customAttributeObject' => [
                'customAttributeType' => SimpleArray::class,
                'inputData' => [
                    'param' => [
                        'customAttributes' => [
                            ['attribute_code' => TestService::CUSTOM_ATTRIBUTE_CODE, 'value' => ['ids' => [1, 2, 3, 4]]]
                        ]
                    ]
                ],
                'expectedObject'=>  $this->getObjectWithCustomAttributes('SimpleArray', ['ids' => [1, 2, 3, 4]]),
            ],
            'customAttributeArrayOfObjects' => [
                'customAttributeType' => 'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple[]',
                'inputData' => [
                    'param' => [
                        'customAttributes' => [
                            ['attribute_code' => TestService::CUSTOM_ATTRIBUTE_CODE, 'value' => [
                                ['entityId' => 14, 'name' => 'First'],
                                ['entityId' => 15, 'name' => 'Second'],
                            ]]
                        ]
                    ]
                ],
                'expectedObject'=>  $this->getObjectWithCustomAttributes('Simple[]', [
                    ['entityId' => 14, 'name' => 'First'],
                    ['entityId' => 15, 'name' => 'Second'],
                ]),
            ],
        ];
    }

    /**
     * Return object with custom attributes
     *
     * @param $type
     * @param array $value
     * @return null|object
     */
    protected function getObjectWithCustomAttributes($type, $value = [])
    {
        $objectManager = new ObjectManager($this);
        $customAttributeValue = null;
        switch ($type) {
            case 'integer':
                $customAttributeValue = $value;
                break;
            case 'SimpleArray':
                $customAttributeValue = $objectManager->getObject(
                    SimpleArray::class,
                    ['data' => $value]
                );
                break;
            case 'Simple[]':
                $dataObjectSimple1 = $objectManager->getObject(
                    Simple::class,
                    ['data' => $value[0]]
                );
                $dataObjectSimple2 = $objectManager->getObject(
                    Simple::class,
                    ['data' => $value[1]]
                );
                $customAttributeValue = [$dataObjectSimple1, $dataObjectSimple2];
                break;
            case 'emptyData':
                return $objectManager->getObject(
                    ObjectWithCustomAttributes::class,
                    ['data' => []]
                );
            default:
                return null;
        }
        return $objectManager->getObject(
            ObjectWithCustomAttributes::class,
            ['data' => [
                'custom_attributes' => [
                    TestService::CUSTOM_ATTRIBUTE_CODE => $objectManager->getObject(
                        AttributeValue::class,
                        ['data' => [
                                'attribute_code' => TestService::CUSTOM_ATTRIBUTE_CODE,
                                'value' => $customAttributeValue
                            ]
                        ]
                    )
                ]
            ]]
        );
    }

    /**
     * Cover invalid custom attribute data
     *
     * @dataProvider invalidCustomAttributesDataProvider
     */
    public function testCustomAttributesExceptions($inputData)
    {
        $this->expectException('Magento\Framework\Webapi\Exception');
        $this->serviceInputProcessor->process(
            TestService::class,
            'ObjectWithCustomAttributesMethod',
            $inputData
        );
    }

    /**
     * @return array
     */
    public function invalidCustomAttributesDataProvider()
    {
        return [
            [
                'inputData' => [
                    'param' => [
                        'customAttributes' => [
                            []
                        ]
                    ]
                ]
            ],
            [
                'inputData' => [
                    'param' => [
                        'customAttributes' => [
                            [
                                'value' => TestService::DEFAULT_VALUE
                            ]
                        ]
                    ]
                ]
            ],
            [
                'inputData' => [
                    'param' => [
                        'customAttributes' => [
                            [
                                'attribute_code' => TestService::CUSTOM_ATTRIBUTE_CODE,
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function payloadDataProvider(): array
    {
        return [
            [
                [
                    'address' => [
                        "street" => [
                            "서울 강북구 한천로166길 2 (-서울 강북구 수유동 269-36)"
                        ],
                        "city." => "pune",
                    ],
                    'cartId' => "30"
                ],
                1
            ],
            [
                [
                    'address' => [
                        "street" => [
                            "서울 강북구 한천로166길 2 (-서울 강북구 수유동 269-36)"
                        ],
                        "city" => "pune",
                    ],
                    'cartId' => "30"
                ],
                0
            ]
        ];
    }

    /**
     * Validate if payload has correct attributes
     *
     * @param array $payload
     * @param int $exception
     * @return void
     * @throws Exception
     * @dataProvider payloadDataProvider
     */
    public function testValidateApiPayload(array $payload, int $exception): void
    {
        if ($exception) {
            $this->expectException(InputException::class);
            $this->expectExceptionMessage('"City." is not supported. Correct the field name and try again.');
        }
        $result = $this->serviceInputProcessor->process(
            ShipmentEstimationInterface::class,
            'estimateByExtendedAddress',
            $payload
        );
        $this->assertNotEmpty($result);
    }
}
