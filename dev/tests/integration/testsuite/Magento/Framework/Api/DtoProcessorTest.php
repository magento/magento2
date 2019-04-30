<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

use Magento\Framework\Api\TestDtoClasses\TestDto;
use Magento\Framework\Api\TestDtoClasses\TestDto2;
use Magento\Framework\Api\TestDtoClasses\TestDtoWithArrays;
use Magento\Framework\Api\TestDtoClasses\TestDtoWithCustomAttributes;
use Magento\Framework\Api\TestDtoClasses\TestHybridDto;
use Magento\Framework\Api\TestDtoClasses\TestMutableDto;
use Magento\Framework\Api\TestDtoClasses\TestNestedDto;
use Magento\Framework\Api\TestDtoClasses\TestSimpleObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class DtoProcessorTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DtoProcessor
     */
    private $dataProcessor;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->dataProcessor = $this->objectManager->get(DtoProcessor::class);
    }

    public function testGetObjectData(): void
    {
        /** @var TestDto $dto */
        $dto = $this->objectManager->create(TestDto::class, [
            'paramOne' => 1,
            'paramTwo' => 2.0,
            'paramThree' => 'test3'
        ]);

        $this->assertEquals(
            [
                'param_one' => 1,
                'param_two' => 2.0,
                'param_three' => 'test3'
            ],
            $this->dataProcessor->getObjectData($dto)
        );
    }

    public function testGetDtoWithCustomAttributes(): void
    {
        /** @var TestDtoWithCustomAttributes $dto */
        $dto = $this->objectManager->create(TestDtoWithCustomAttributes::class, [
            'paramOne' => 1,
            'paramTwo' => 2.0
        ]);

        $dto->setCustomAttribute('my_custom_attribute', 'test3');

        $this->assertEquals(
            [
                'param_one' => 1,
                'param_two' => 2.0,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'my_custom_attribute',
                        'value' => 'test3'
                    ]
                ]
            ],
            $this->dataProcessor->getObjectData($dto)
        );
    }

    public function testGetObjectDataWithWrongTypes(): void
    {
        /** @var TestDto $dto */
        $dto = $this->objectManager->create(TestDto::class, [
            'paramOne' => '1',
            'paramTwo' => '2.0',
            'paramThree' => 'test3'
        ]);

        $res = $this->dataProcessor->getObjectData($dto);
        $this->assertSame(1, $res['param_one']);
        $this->assertSame(2.0, $res['param_two']);
    }

    public function testGetNestedObjectData(): void
    {
        $dto1 = $this->objectManager->create(TestDto::class, [
            'paramOne' => 1,
            'paramTwo' => 2.0,
            'paramThree' => 'test1-3'
        ]);
        $dto2 = $this->objectManager->create(TestDto::class, [
            'paramOne' => 2,
            'paramTwo' => 4.0,
            'paramThree' => 'test2-3'
        ]);

        $dtoArray = [];
        for ($i=0; $i<2; $i++) {
            $dtoArray[] = $this->objectManager->create(TestDto::class, [
                'paramOne' => $i * 3 + 3,
                'paramTwo' => $i * 3.0 + 4.0,
                'paramThree' => 'array' . $i . '-3'
            ]);
        }

        $nestedDto = $this->objectManager->create(TestNestedDto::class, [
            'id' => 'my-id',
            'testDto1' => $dto1,
            'testDto2' => $dto2,
            'testDtoArray' => $dtoArray
        ]);

        $this->assertEquals(
            [
                'id' => 'my-id',
                'test_dto1' => [
                    'param_one' => 1,
                    'param_two' => 2.0,
                    'param_three' => 'test1-3'
                ],
                'test_dto2' => [
                    'param_one' => 2,
                    'param_two' => 4.0,
                    'param_three' => 'test2-3'
                ],
                'test_dto_array' => [
                    [
                        'param_one' => 3,
                        'param_two' => 4.0,
                        'param_three' => 'array0-3'
                    ],
                    [
                        'param_one' => 6,
                        'param_two' => 7.0,
                        'param_three' => 'array1-3'
                    ]
                ]
            ],
            $this->dataProcessor->getObjectData($nestedDto)
        );
    }

    public function testCreateDtoFromArrayWithCustomAttributesAsKeyValueFormat(): void
    {
        /** @var TestDtoWithCustomAttributes $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'param_one' => 1,
                'param_two' => 2.0,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'my_custom_attribute',
                        'value' => 'test3'
                    ]
                ]
            ],
            TestDtoWithCustomAttributes::class
        );

        $this->assertSame(1, $dto->getParamOne());
        $this->assertSame(2.0, $dto->getParamTwo());
        $this->assertSame('test3', $dto->getCustomAttribute('my_custom_attribute')->getValue());
    }

    public function testCreateDtoFromArrayWithCustomAttributes(): void
    {
        /** @var TestDtoWithCustomAttributes $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'param_one' => 1,
                'param_two' => 2.0,
                'custom_attributes' => [
                    'my_custom_attribute' => 'test3'
                ]
            ],
            TestDtoWithCustomAttributes::class
        );

        $this->assertSame(1, $dto->getParamOne());
        $this->assertSame(2.0, $dto->getParamTwo());
        $this->assertSame('test3', $dto->getCustomAttribute('my_custom_attribute')->getValue());
    }

    public function testCreateImmutableDtoFromArray(): void
    {
        /** @var TestDto $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'param_one' => 1,
                'param_two' => 2.0,
                'param_three' => 'test3'
            ],
            TestDto::class
        );

        $this->assertSame(1, $dto->getParamOne());
        $this->assertSame(2.0, $dto->getParamTwo());
        $this->assertSame('test3', $dto->getParamThree());
    }

    public function testCreateImmutableDtoWithNumericParametersFromArray(): void
    {
        /** @var TestDto2 $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'param1' => 1,
                'param2' => 2.0,
                'param3' => 'test3'
            ],
            TestDto2::class
        );

        $this->assertSame(1, $dto->getParam1());
        $this->assertSame(2.0, $dto->getParam2());
        $this->assertSame('test3', $dto->getParam3());
    }

    public function testCreateWithCamelCaseParameters(): void
    {
        /** @var TestDto $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'paramOne' => 1,
                'paramTwo' => 2.0,
                'paramThree' => 'test3'
            ],
            TestDto::class
        );

        $this->assertSame(1, $dto->getParamOne());
        $this->assertSame(2.0, $dto->getParamTwo());
        $this->assertSame('test3', $dto->getParamThree());
    }

    public function testCreateImmutableDtoWithArraysFromArray(): void
    {
        /** @var TestDtoWithArrays $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'param_one' => ['1', 2, '3', 4],
                'param_two' => ['a' => 1, 'b' => 2]
            ],
            TestDtoWithArrays::class
        );

        $this->assertSame([1, 2, 3, 4], $dto->getParamOne());
        $this->assertSame(['a' => 1, 'b' => 2], $dto->getParamTwo());
    }

    public function testCreateNestedImmutableDtoFromArray(): void
    {
        /** @var TestNestedDto $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'id' => 'my-id',
                'test_dto1' => [
                    'param_one' => 1,
                    'param_two' => 2.0,
                    'param_three' => 'test1-3'
                ],
                'test_dto2' => [
                    'param_one' => 2,
                    'param_two' => 4.0,
                    'param_three' => 'test2-3'
                ],
                'test_dto_array' => [
                    [
                        'param_one' => 3,
                        'param_two' => 6.0,
                        'param_three' => 'array0-3'
                    ],
                    [
                        'param_one' => 4,
                        'param_two' => 8.0,
                        'param_three' => 'array1-3'
                    ]
                ]
            ],
            TestNestedDto::class
        );

        $this->assertSame('my-id', $dto->getId());

        $this->assertSame(1, $dto->getTestDto1()->getParamOne());
        $this->assertSame(2.0, $dto->getTestDto1()->getParamTwo());
        $this->assertSame('test1-3', $dto->getTestDto1()->getParamThree());

        $this->assertSame(2, $dto->getTestDto2()->getParamOne());
        $this->assertSame(4.0, $dto->getTestDto2()->getParamTwo());
        $this->assertSame('test2-3', $dto->getTestDto2()->getParamThree());

        $this->assertCount(2, $dto->getTestDtoArray());

        $this->assertSame(3, $dto->getTestDtoArray()[0]->getParamOne());
        $this->assertSame(6.0, $dto->getTestDtoArray()[0]->getParamTwo());
        $this->assertSame('array0-3', $dto->getTestDtoArray()[0]->getParamThree());

        $this->assertSame(4, $dto->getTestDtoArray()[1]->getParamOne());
        $this->assertSame(8.0, $dto->getTestDtoArray()[1]->getParamTwo());
        $this->assertSame('array1-3', $dto->getTestDtoArray()[1]->getParamThree());
    }

    public function testCreateUpdatedDataObject(): void
    {
        /** @var TestDto $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'param_one' => 1,
                'param_two' => 2.0,
                'param_three' => 'test3'
            ],
            TestDto::class
        );

        /** @var TestDto $updatedDto */
        $updatedDto = $this->dataProcessor->createUpdatedObjectFromArray(
            $dto,
            [
                'param_two' => 3.0
            ]
        );

        $this->assertNotSame($dto, $updatedDto);

        $this->assertSame(2.0, $dto->getParamTwo());

        $this->assertSame(1, $updatedDto->getParamOne());
        $this->assertSame(3.0, $updatedDto->getParamTwo());
        $this->assertSame('test3', $updatedDto->getParamThree());
    }

    public function testCreateUpdatedNestedDataObject(): void
    {
        /** @var TestNestedDto $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'id' => 'my-id',
                'test_dto1' => [
                    'param_one' => 1,
                    'param_two' => 2.0,
                    'param_three' => 'test1-3'
                ],
                'test_dto2' => [
                    'param_one' => 2,
                    'param_two' => 4.0,
                    'param_three' => 'test2-3'
                ],
                'test_dto_array' => [
                    [
                        'param_one' => 3,
                        'param_two' => 6.0,
                        'param_three' => 'array0-3'
                    ],
                    [
                        'param_one' => 4,
                        'param_two' => 8.0,
                        'param_three' => 'array1-3'
                    ]
                ]
            ],
            TestNestedDto::class
        );

        /** @var TestNestedDto $updatedDto */
        $updatedDto = $this->dataProcessor->createUpdatedObjectFromArray(
            $dto,
            [
                'test_dto1' => [
                    'param_one' => 2,
                    'param_two' => 3.0,
                    'param_three' => 'test1-4'
                ],
                'test_dto_array' => [
                    [
                        'param_one' => '11',
                        'param_two' => '12',
                        'param_three' => 'array3-3'
                    ],
                    [
                        'param_one' => '13',
                        'param_two' => '14',
                        'param_three' => 'array4-3'
                    ]
                ]
            ]
        );

        $this->assertNotSame($dto, $updatedDto);

        $this->assertSame('my-id', $updatedDto->getId());

        $this->assertSame(2, $updatedDto->getTestDto1()->getParamOne());
        $this->assertSame(3.0, $updatedDto->getTestDto1()->getParamTwo());
        $this->assertSame('test1-4', $updatedDto->getTestDto1()->getParamThree());

        $this->assertSame(2, $updatedDto->getTestDto2()->getParamOne());
        $this->assertSame(4.0, $updatedDto->getTestDto2()->getParamTwo());
        $this->assertSame('test2-3', $updatedDto->getTestDto2()->getParamThree());

        $this->assertCount(2, $updatedDto->getTestDtoArray());

        $this->assertSame(11, $updatedDto->getTestDtoArray()[0]->getParamOne());
        $this->assertSame(12.0, $updatedDto->getTestDtoArray()[0]->getParamTwo());
        $this->assertSame('array3-3', $updatedDto->getTestDtoArray()[0]->getParamThree());

        $this->assertSame(13, $updatedDto->getTestDtoArray()[1]->getParamOne());
        $this->assertSame(14.0, $updatedDto->getTestDtoArray()[1]->getParamTwo());
        $this->assertSame('array4-3', $updatedDto->getTestDtoArray()[1]->getParamThree());
    }

    public function testCreateMutableDataObject(): void
    {
        /** @var TestMutableDto $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'param_one' => 'test1',
                'param_two' => 'test2',
            ],
            TestMutableDto::class
        );

        $this->assertSame('test1', $dto->getParamOne());
        $this->assertSame('test2', $dto->getParamTwo());
    }

    public function testCreateMutableDataObjectWithInvalidParameters(): void
    {
        /** @var TestMutableDto $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'param_one' => 'test1',
                'param_two' => 'test2',
                'invalid_param' => 'test3'
            ],
            TestMutableDto::class
        );

        $this->assertSame('test1', $dto->getParamOne());
        $this->assertSame('test2', $dto->getParamTwo());
    }

    public function testCreateHybridDataObject(): void
    {
        /** @var TestHybridDto $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'immutable_one' => 'test1',
                'immutable_two' => 'test2',
                'mutable_three' => 'test3',
                'mutable_four' => 'test4',
                'mutable_five' => 'test5',
                'mutable_six' => 'test6',
            ],
            TestHybridDto::class
        );

        $this->assertSame('test1', $dto->getImmutableOne());
        $this->assertSame('test2', $dto->getImmutableTwo());
        $this->assertSame('test3', $dto->getMutableThree());
        $this->assertSame('test4', $dto->getMutableFour());
        $this->assertSame('test5', $dto->getMutableFive());
        $this->assertSame('test6', $dto->getMutableSix());
    }

    /**
     * @return array
     */
    public function hydratingStrategyDataProvider(): array
    {
        return [
            'simpleDto' => [
                TestDto::class,
                [
                    'param_one' => 'test1',
                    'param_two' => 'test2',
                    'param_three' => 'test3'
                ],
                [
                    DtoProcessor::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [
                        'param_one' => [
                            'parameter' => 'paramOne',
                            'type' => 'int'
                        ],
                        'param_two' => [
                            'parameter' => 'paramTwo',
                            'type' => 'float'
                        ],
                        'param_three' => [
                            'parameter' => 'paramThree',
                            'type' => 'string'
                        ],
                    ],
                    DtoProcessor::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
                    DtoProcessor::HYDRATOR_STRATEGY_SETTER => [],
                    DtoProcessor::HYDRATOR_STRATEGY_ORPHAN => []
                ]
            ],
            'simpleDtoWithOrphanParameter' => [
                TestDto::class,
                [
                    'param_one' => 'test1',
                    'param_two' => 'test2',
                    'param_three' => 'test3',
                    'orphan_parameter' => 'test4'
                ],
                [
                    DtoProcessor::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [
                        'param_one' => [
                            'parameter' => 'paramOne',
                            'type' => 'int'
                        ],
                        'param_two' => [
                            'parameter' => 'paramTwo',
                            'type' => 'float'
                        ],
                        'param_three' => [
                            'parameter' => 'paramThree',
                            'type' => 'string'
                        ],
                    ],
                    DtoProcessor::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
                    DtoProcessor::HYDRATOR_STRATEGY_SETTER => [],
                    DtoProcessor::HYDRATOR_STRATEGY_ORPHAN => [
                        'orphan_parameter'
                    ]
                ]
            ],
            'simpleDataObject' => [
                TestSimpleObject::class,
                [
                    'param_one' => 'test1',
                    'param_two' => 'test2',
                    'param_three' => 'test3'
                ],
                [
                    DtoProcessor::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [],
                    DtoProcessor::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [
                        'param_one' => [
                            'type' => 'string'
                        ],
                        'param_two' => [
                            'type' => 'string'
                        ],
                        'param_three' => [
                            'type' => 'string'
                        ],
                    ],
                    DtoProcessor::HYDRATOR_STRATEGY_SETTER => [],
                    DtoProcessor::HYDRATOR_STRATEGY_ORPHAN => []
                ]
            ],
            'hybridObject' => [
                TestHybridDto::class,
                [
                    'immutable_one' => 'test1',
                    'immutable_two' => 'test2',
                    'mutable_three' => 'test3',
                    'mutable_four' => 'test4',
                    'mutable_five' => 'test5',
                    'mutable_six' => 'test6',
                ],
                [
                    DtoProcessor::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [
                        'immutable_one' => [
                            'parameter' => 'immutableOne',
                            'type' => 'string',
                        ],
                        'immutable_two' => [
                            'parameter' => 'immutableTwo',
                            'type' => 'string',
                        ],
                        'mutable_three' => [
                            'parameter' => 'mutableThree',
                            'type' => 'string',
                        ],
                        'mutable_four' => [
                            'parameter' => 'mutableFour',
                            'type' => 'string',
                        ]
                    ],
                    DtoProcessor::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
                    DtoProcessor::HYDRATOR_STRATEGY_SETTER => [
                        'mutable_five' => [
                            'method' => 'setMutableFive',
                            'type' => 'string'
                        ],
                        'mutable_six' => [
                            'method' => 'setMutableSix',
                            'type' => 'string'
                        ]
                    ],
                    DtoProcessor::HYDRATOR_STRATEGY_ORPHAN => []
                ]
            ]
        ];
    }

    /**
     * @param string $className
     * @param array $dataPayload
     * @param array $expectedResult
     * @throws ReflectionException
     * @dataProvider hydratingStrategyDataProvider
     */
    public function testHydratingStrategy(string $className, array $dataPayload, array $expectedResult): void
    {
        $res = $this->dataProcessor->getValuesHydratingStrategy($className, $dataPayload);

        $this->assertEquals($expectedResult, $res);
    }
}
