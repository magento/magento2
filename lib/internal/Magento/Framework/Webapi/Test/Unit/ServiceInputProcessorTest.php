<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Webapi\Test\Unit;

use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\WebapiBuilderFactory;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\AssociativeArray;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\DataArray;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\ObjectWithCustomAttributes;
use Magento\Webapi\Test\Unit\Service\Entity\DataArrayData;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Nested;
use Magento\Webapi\Test\Unit\Service\Entity\NestedData;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\SimpleArray;
use Magento\Webapi\Test\Unit\Service\Entity\SimpleArrayData;
use Magento\Webapi\Test\Unit\Service\Entity\SimpleData;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService;

class ServiceInputProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceInputProcessor */
    protected $serviceInputProcessor;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $attributeValueFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $customAttributeTypeLocator;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $objectManagerMock;

    /** @var  \Magento\Framework\Reflection\MethodsMap */
    protected $methodsMap;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $fieldNamer;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder('\Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function ($className) use ($objectManager) {
                    return $objectManager->getObject($className);
                }
            );

        /** @var \Magento\Framework\Reflection\TypeProcessor $typeProcessor */
        $typeProcessor = $objectManager->getObject('Magento\Framework\Reflection\TypeProcessor');
        $cache = $this->getMockBuilder('Magento\Framework\App\Cache\Type\Reflection')
            ->disableOriginalConstructor()
            ->getMock();
        $cache->expects($this->any())->method('load')->willReturn(false);

        $this->customAttributeTypeLocator = $this->getMockBuilder('Magento\Eav\Model\EavCustomAttributeTypeLocator')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\Api\AttributeDataBuilder */
        $this->attributeValueFactoryMock = $this->getMockBuilder('Magento\Framework\Api\AttributeValueFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeValueFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () use ($objectManager) {
                    return $objectManager->getObject('Magento\Framework\Api\AttributeValue');
                }
            );

        $this->fieldNamer = $this->getMockBuilder('Magento\Framework\Reflection\FieldNamer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->methodsMap = $objectManager->getObject(
            'Magento\Framework\Reflection\MethodsMap',
            [
                'cache' => $cache,
                'typeProcessor' => $typeProcessor,
                'attributeTypeResolver' => $this->attributeValueFactoryMock->create(),
                'fieldNamer' => $this->fieldNamer
            ]
        );

        $this->serviceInputProcessor = $objectManager->getObject(
            'Magento\Framework\Webapi\ServiceInputProcessor',
            [
                'typeProcessor' => $typeProcessor,
                'objectManager' => $this->objectManagerMock,
                'customAttributeTypeLocator' => $this->customAttributeTypeLocator,
                'attributeValueFactory' => $this->attributeValueFactoryMock,
                'methodsMap' => $this->methodsMap
            ]
        );

        /** @var \Magento\Framework\Reflection\NameFinder $nameFinder */
        $nameFinder = $objectManager->getObject('\Magento\Framework\Reflection\NameFinder');
        $serviceInputProcessorReflection = new \ReflectionClass(get_class($this->serviceInputProcessor));
        $typeResolverReflection = $serviceInputProcessorReflection->getProperty('nameFinder');
        $typeResolverReflection->setAccessible(true);
        $typeResolverReflection->setValue($this->serviceInputProcessor, $nameFinder);
    }

    public function testSimpleProperties()
    {
        $data = ['entityId' => 15, 'name' => 'Test'];
        $result = $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
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
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'simpleDefaultValue',
            $data
        );
        $this->assertNotNull($result);
        $this->assertEquals(TestService::DEFAULT_VALUE, $result[0]);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage One or more input exceptions have occurred.
     */
    public function testNonExistentPropertiesWithoutDefaultArgumentValue()
    {
        $data = [];
        $result = $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'simple',
            $data
        );
        $this->assertNull($result);
    }

    public function testNestedDataProperties()
    {
        $data = ['nested' => ['details' => ['entityId' => 15, 'name' => 'Test']]];
        $result = $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'nestedData',
            $data
        );
        $this->assertNotNull($result);
        $this->assertTrue($result[0] instanceof Nested);
        /** @var array $result */
        $this->assertEquals(1, count($result));
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

    public function testSimpleArrayProperties()
    {
        $data = ['ids' => [1, 2, 3, 4]];
        $result = $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'simpleArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $ids */
        $ids = $result[0];
        $this->assertNotNull($ids);
        $this->assertEquals(4, count($ids));
        $this->assertEquals($data['ids'], $ids);
    }

    public function testAssociativeArrayProperties()
    {
        $data = ['associativeArray' => ['key' => 'value', 'key_two' => 'value_two']];
        $result = $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
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
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $associativeArray */
        $associativeArray = $result[0];
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray[0]);
    }

    public function testAssociativeArrayPropertiesWithItemArray()
    {
        $data = ['associativeArray' => ['item' => ['value1','value2']]];
        $result = $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
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
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'dataArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $dataObjects */
        $dataObjects = $result[0];
        $this->assertEquals(2, count($dataObjects));
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

    public function testNestedSimpleArrayProperties()
    {
        $data = ['arrayData' => ['ids' => [1, 2, 3, 4]]];
        $result = $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'nestedSimpleArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var SimpleArrayData $dataObject */
        $dataObject = $result[0];
        $this->assertTrue($dataObject instanceof SimpleArray);
        /** @var array $ids */
        $ids = $dataObject->getIds();
        $this->assertNotNull($ids);
        $this->assertEquals(4, count($ids));
        $this->assertEquals($data['arrayData']['ids'], $ids);
    }

    public function testNestedAssociativeArrayProperties()
    {
        $data = [
            'associativeArrayData' => ['associativeArray' => ['key' => 'value', 'key2' => 'value2']],
        ];
        $result = $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'nestedAssociativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var \Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\AssociativeArray $dataObject */
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
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'nestedDataArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var DataArrayData $dataObjects */
        $dataObjects = $result[0];
        $this->assertTrue($dataObjects instanceof DataArray);
        /** @var array $items */
        $items = $dataObjects->getItems();
        $this->assertEquals(2, count($items));
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

        $result = $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
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
                'customAttributeType' => 'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\SimpleArray',
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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $customAttributeValue = null;
        switch($type) {
            case 'integer':
                $customAttributeValue = $value;
                break;
            case 'SimpleArray':
                $customAttributeValue = $objectManager->getObject(
                    'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\SimpleArray',
                    ['data' => $value]
                );
                break;
            case 'Simple[]':
                $dataObjectSimple1 = $objectManager->getObject(
                    'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple',
                    ['data' => $value[0]]
                );
                $dataObjectSimple2 = $objectManager->getObject(
                    'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple',
                    ['data' => $value[1]]
                );
                $customAttributeValue = [$dataObjectSimple1, $dataObjectSimple2];
                break;
            case 'emptyData':
                return $objectManager->getObject(
                    'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\ObjectWithCustomAttributes',
                    ['data' => []]
                );
            default:
                return null;
        }
        return $objectManager->getObject(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\ObjectWithCustomAttributes',
            ['data' => [
                'custom_attributes' => [
                    TestService::CUSTOM_ATTRIBUTE_CODE => $objectManager->getObject(
                        'Magento\Framework\Api\AttributeValue',
                        ['data' =>
                            [
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
     * @expectedException \Magento\Framework\Webapi\Exception
     */
    public function testCustomAttributesExceptions($inputData)
    {
        $this->serviceInputProcessor->process(
            'Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\TestService',
            'ObjectWithCustomAttributesMethod',
            $inputData
        );
    }

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
}
