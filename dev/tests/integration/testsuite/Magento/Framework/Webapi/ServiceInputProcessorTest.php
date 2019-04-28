<?php
/**
 * Test case for \Magento\Framework\Profiler
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi;

use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\ServiceInputProcessor\AssociativeArray;
use Magento\Framework\Webapi\ServiceInputProcessor\DataArray;
use Magento\Framework\Webapi\ServiceInputProcessor\Nested;
use Magento\Framework\Webapi\ServiceInputProcessor\ObjectWithCustomAttributes;
use Magento\Framework\Webapi\ServiceInputProcessor\Simple;
use Magento\Framework\Webapi\ServiceInputProcessor\SimpleArray;
use Magento\Framework\Webapi\ServiceInputProcessor\SimpleImmutable;
use Magento\Framework\Webapi\ServiceInputProcessor\TestService;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ServiceInputProcessorTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ServiceInputProcessor
     */
    private $serviceInputProcessor;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->serviceInputProcessor = $this->objectManager->get(ServiceInputProcessor::class);
    }

    public function testSimpleProperties(): void
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

    public function testSimpleImmutableProperties(): void
    {
        $data = ['simpleImmutable' => ['entityId' => 15, 'name' => 'Test']];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'simpleImmutable',
            $data
        );
        $this->assertNotNull($result);

        /** @var SimpleImmutable $arg */
        $arg = $result[0];
        $this->assertInstanceOf(SimpleImmutable::class, $arg);
        $this->assertEquals(15, $arg->getEntityId());
        $this->assertEquals('Test', $arg->getName());
    }

    public function testNonExistentPropertiesWithDefaultArgumentValue(): void
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

    public function testNonExistentPropertiesWithoutDefaultArgumentValue(): void
    {
        $data = [];

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('One or more input exceptions have occurred.');
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'simple',
            $data
        );
        $this->assertNull($result);
    }

    public function testNestedDataProperties(): void
    {
        $data = ['nested' => ['details' => ['entityId' => 15, 'name' => 'Test']]];
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'nestedData',
            $data
        );
        $this->assertNotNull($result);
        $this->assertInstanceOf(Nested::class, $result[0]);
        /** @var array $result */
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]);
        /** @var Nested $arg */
        $arg = $result[0];
        $this->assertInstanceOf(Nested::class, $arg);
        /** @var Simple $details */
        $details = $arg->getDetails();
        $this->assertNotNull($details);
        $this->assertInstanceOf(Simple::class, $details);
        $this->assertEquals(15, $details->getEntityId());
        $this->assertEquals('Test', $details->getName());
    }

    public function testSimpleArrayProperties(): void
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

    public function testAssociativeArrayProperties(): void
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

    public function testAssociativeArrayPropertiesWithItem(): void
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

    public function testAssociativeArrayPropertiesWithItemArray(): void
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

    public function testArrayOfDataObjectProperties(): void
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

        /** @var Simple $first */
        $first = $dataObjects[0];

        /** @var Simple $second */
        $second = $dataObjects[1];

        $this->assertInstanceOf(Simple::class, $first);
        $this->assertEquals(14, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertInstanceOf(Simple::class, $second);
        $this->assertEquals(15, $second->getEntityId());
        $this->assertEquals('Second', $second->getName());
    }

    public function testNestedSimpleArrayProperties(): void
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
        $this->assertInstanceOf(SimpleArray::class, $dataObject);

        /** @var array $ids */
        $ids = $dataObject->getIds();
        $this->assertNotNull($ids);
        $this->assertCount(4, $ids);
        $this->assertEquals($data['arrayData']['ids'], $ids);
    }

    public function testNestedAssociativeArrayProperties(): void
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
        $this->assertInstanceOf(AssociativeArray::class, $dataObject);

        /** @var array $associativeArray */
        $associativeArray = $dataObject->getAssociativeArray();
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray['key']);
        $this->assertEquals('value2', $associativeArray['key2']);
    }

    public function testNestedArrayOfDataObjectProperties(): void
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

        /** @var DataArray $dataObjects */
        $dataObjects = $result[0];
        $this->assertInstanceOf(DataArray::class, $dataObjects);

        /** @var array $items */
        $items = $dataObjects->getItems();
        $this->assertCount(2, $items);

        /** @var Simple $first */
        $first = $items[0];

        /** @var Simple $second */
        $second = $items[1];

        $this->assertInstanceOf(Simple::class, $first);
        $this->assertEquals(1, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertInstanceOf(Simple::class, $second);
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
     * @throws Exception
     * @throws InputException
     * @throws LocalizedException
     */
    public function testCustomAttributesProperties($customAttributeType, $inputData, $expectedObject): void
    {
        $result = $this->serviceInputProcessor->process(
            TestService::class,
            'ObjectWithCustomAttributesMethod',
            $inputData
        );

        $this->assertInstanceOf(ObjectWithCustomAttributes::class, $result[0]);
        $this->assertEquals($expectedObject, $result[0]);
    }

    /**
     * Provides data for testCustomAttributesProperties
     *
     * @return array
     */
    public function customAttributesDataProvider(): array
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
        $objectManager = Bootstrap::getObjectManager();

        $customAttributeValue = null;
        switch ($type) {
            case 'integer':
                $customAttributeValue = $value;
                break;
            case 'SimpleArray':
                $customAttributeValue = $objectManager->create(
                    SimpleArray::class,
                    ['data' => $value]
                );
                break;
            case 'Simple[]':
                $dataObjectSimple1 = $objectManager->create(
                    Simple::class,
                    ['data' => $value[0]]
                );
                $dataObjectSimple2 = $objectManager->create(
                    Simple::class,
                    ['data' => $value[1]]
                );
                $customAttributeValue = [$dataObjectSimple1, $dataObjectSimple2];
                break;
            case 'emptyData':
                return $objectManager->create(
                    ObjectWithCustomAttributes::class,
                    ['data' => []]
                );
            default:
                return null;
        }

        return $objectManager->create(
            ObjectWithCustomAttributes::class,
            ['data' => [
                'custom_attributes' => [
                    TestService::CUSTOM_ATTRIBUTE_CODE => $objectManager->create(
                        AttributeValue::class,
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
     * @expectedException Exception
     * @param array $inputData
     * @throws Exception
     * @throws InputException
     * @throws LocalizedException
     */
    public function testCustomAttributesExceptions(array $inputData): void
    {
        $this->serviceInputProcessor->process(
            TestService::class,
            'ObjectWithCustomAttributes',
            $inputData
        );
    }

    /**
     * @return array
     */
    public function invalidCustomAttributesDataProvider(): array
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
