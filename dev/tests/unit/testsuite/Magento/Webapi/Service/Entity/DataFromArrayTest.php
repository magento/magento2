<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Webapi\Service\Entity\TestService;

class DataFromArrayTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceInputProcessor */
    protected $serviceInputProcessor;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $attributeValueBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $serviceConfigReader;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $objectFactory = new \Magento\Webapi\Service\Entity\WebapiBuilderFactory($objectManager);
        /** @var \Magento\Framework\Reflection\TypeProcessor $typeProcessor */
        $typeProcessor = $objectManager->getObject('Magento\Framework\Reflection\TypeProcessor');
        $cache = $this->getMockBuilder('Magento\Webapi\Model\Cache\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $cache->expects($this->any())->method('load')->willReturn(false);

        $this->serviceConfigReader = $this->getMockBuilder('Magento\Framework\Api\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\Api\AttributeDataBuilder */
        $this->attributeValueBuilder = $objectManager->getObject('Magento\Framework\Api\AttributeDataBuilder');

        $this->serviceInputProcessor = $objectManager->getObject(
            'Magento\Framework\Webapi\ServiceInputProcessor',
            [
                'typeProcessor' => $typeProcessor,
                'builderFactory' => $objectFactory,
                'cache' => $cache,
                'serviceConfigReader' => $this->serviceConfigReader,
                'attributeValueBuilder' => $this->attributeValueBuilder
            ]
        );
    }

    public function testSimpleProperties()
    {
        $data = ['entityId' => 15, 'name' => 'Test'];
        $result = $this->serviceInputProcessor->process(
            'Magento\Webapi\Service\Entity\TestService',
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
            'Magento\Webapi\Service\Entity\TestService',
            'simpleDefaultValue',
            $data
        );
        $this->assertNotNull($result);
        $this->assertEquals(\Magento\Webapi\Service\Entity\TestService::DEFAULT_VALUE, $result[0]);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage \Magento\Framework\Exception\InputException::DEFAULT_MESSAGE
     */
    public function testNonExistentPropertiesWithoutDefaultArgumentValue()
    {
        $data = [];
        $result = $this->serviceInputProcessor->process(
            'Magento\Webapi\Service\Entity\TestService',
            'simple',
            $data
        );
        $this->assertNull($result);
    }

    public function testNestedDataProperties()
    {
        $data = ['nested' => ['details' => ['entityId' => 15, 'name' => 'Test']]];
        $result = $this->serviceInputProcessor->process(
            'Magento\Webapi\Service\Entity\TestService',
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
            'Magento\Webapi\Service\Entity\TestService',
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
            'Magento\Webapi\Service\Entity\TestService',
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
            'Magento\Webapi\Service\Entity\TestService',
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
            'Magento\Webapi\Service\Entity\TestService',
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
            'Magento\Webapi\Service\Entity\TestService',
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
            'Magento\Webapi\Service\Entity\TestService',
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
            'Magento\Webapi\Service\Entity\TestService',
            'nestedAssociativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
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
            'Magento\Webapi\Service\Entity\TestService',
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
     * @param $customAttributeValue
     * @param $attributeCode
     */
    public function testCustomAttributesProperties($customAttributeType, $inputData, $expectedObject)
    {
        $this->serviceConfigReader->expects($this->any())->method('read')->willReturn(
            [
                'Magento\Webapi\Service\Entity\ObjectWithCustomAttributes' => [
                    TestService::CUSTOM_ATTRIBUTE_CODE => $customAttributeType
                ]
            ]
        );

        $result = $this->serviceInputProcessor->process(
            'Magento\Webapi\Service\Entity\TestService',
            'ObjectWithCustomAttributesMethod',
            $inputData
        );

        $this->assertTrue($result[0] instanceof \Magento\Webapi\Service\Entity\ObjectWithCustomAttributes);
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
                'customAttributeType' => 'Magento\Webapi\Service\Entity\SimpleArray',
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
                'customAttributeType' => 'Magento\Webapi\Service\Entity\Simple[]',
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
            'customAttributeNonExistentCustomAttributeCode' => [
                'customAttributeType' => 'integer',
                'inputData' => [
                    'param' => [
                        'customAttributes' => [
                            [
                                'non_existent_attribute_code_' => TestService::CUSTOM_ATTRIBUTE_CODE,
                                'value' => TestService::DEFAULT_VALUE
                            ]
                        ]
                    ]
                ],
                'expectedObject'=>   $this->getObjectWithCustomAttributes('emptyData')
            ],
            'customAttributeObjectNonExistentCustomAttributeCodeValue' => [
                'customAttributeType' => 'Magento\Webapi\Service\Entity\SimpleArray',
                'inputData' => [
                    'param' => [
                        'customAttributes' => [
                            ['attribute_code' => 'nonExistentAttributeCode', 'value' => ['ids' => [1, 2, 3, 4]]]
                        ]
                    ]
                ],
                'expectedObject'=>   $this->getObjectWithCustomAttributes('emptyData')
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
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $customAttributeValue = null;
        switch($type) {
            case 'integer':
                $customAttributeValue = $value;
                break;
            case 'SimpleArray':
                $customAttributeValue = $objectManager->getObject(
                    'Magento\Webapi\Service\Entity\SimpleArray',
                    ['data' => $value]
                );
                break;
            case 'Simple[]':
                $dataObjectSimple1 = $objectManager->getObject(
                    'Magento\Webapi\Service\Entity\Simple',
                    ['data' => $value[0]]
                );
                $dataObjectSimple2 = $objectManager->getObject(
                    'Magento\Webapi\Service\Entity\Simple',
                    ['data' => $value[1]]
                );
                $customAttributeValue = [$dataObjectSimple1, $dataObjectSimple2];
                break;
            case 'emptyData':
                return $objectManager->getObject(
                    'Magento\Webapi\Service\Entity\ObjectWithCustomAttributes',
                    ['data' => []]

                );
            default:
                return null;
        }
        return $objectManager->getObject(
            'Magento\Webapi\Service\Entity\ObjectWithCustomAttributes',
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
}
