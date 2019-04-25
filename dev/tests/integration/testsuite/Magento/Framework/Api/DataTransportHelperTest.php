<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

use Magento\Framework\Api\TestDtoClasses\TestDto;
use Magento\Framework\Api\TestDtoClasses\TestExtensibleDto;
use Magento\Framework\Api\TestDtoClasses\TestExtensibleDtoExtension;
use Magento\Framework\Api\TestDtoClasses\TestExtensibleDtoExtensionInterface;
use Magento\Framework\Api\TestDtoClasses\TestExtensibleDtoInterface;
use Magento\Framework\Api\TestDtoClasses\TestHybridDto;
use Magento\Framework\Api\TestDtoClasses\TestMutableDto;
use Magento\Framework\Api\TestDtoClasses\TestNestedDto;
use Magento\Framework\Api\TestDtoClasses\TestSimpleObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class DataTransportHelperTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataTransportHelper
     */
    private $dataTransportHelper;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->dataTransportHelper = $this->objectManager->get(DataTransportHelper::class);

        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                TestExtensibleDtoInterface::class => TestExtensibleDto::class,
                TestExtensibleDtoExtensionInterface::class => TestExtensibleDtoExtension::class,
            ]
        ]);
    }

    public function testGetObjectData(): void
    {
        /** @var TestDto $dto */
        $dto = $this->objectManager->create(TestDto::class, [
            'paramOne' => 'test1',
            'paramTwo' => 'test2',
            'paramThree' => 'test3'
        ]);

        $this->assertSame(
            [
                'param_one' => 'test1',
                'param_two' => 'test2',
                'param_three' => 'test3'
            ],
            $this->dataTransportHelper->getObjectData($dto)
        );
    }

    public function testGetNestedObjectData(): void
    {
        $dto1 = $this->objectManager->create(TestDto::class, [
            'paramOne' => 'test1-1',
            'paramTwo' => 'test1-2',
            'paramThree' => 'test1-3'
        ]);
        $dto2 = $this->objectManager->create(TestDto::class, [
            'paramOne' => 'test2-1',
            'paramTwo' => 'test2-2',
            'paramThree' => 'test2-3'
        ]);

        $nestedDto = $this->objectManager->create(TestNestedDto::class, [
            'id' => 'my-id',
            'testDto1' => $dto1,
            'testDto2' => $dto2
        ]);

        $this->assertSame(
            [
                'id' => 'my-id',
                'test_dto1' => [
                    'param_one' => 'test1-1',
                    'param_two' => 'test1-2',
                    'param_three' => 'test1-3'
                ],
                'test_dto2' => [
                    'param_one' => 'test2-1',
                    'param_two' => 'test2-2',
                    'param_three' => 'test2-3'
                ]
            ],
            $this->dataTransportHelper->getObjectData($nestedDto)
        );
    }

    public function testCreateImmutableDtoFromArray(): void
    {
        /** @var TestDto $dto */
        $dto = $this->dataTransportHelper->createFromArray(
            [
                'param_one' => 'test1',
                'param_two' => 'test2',
                'param_three' => 'test3'
            ],
            TestDto::class
        );

        $this->assertSame('test1', $dto->getParamOne());
        $this->assertSame('test2', $dto->getParamTwo());
        $this->assertSame('test3', $dto->getParamThree());
    }

    public function testCreateNestedImmutableDtoFromArray(): void
    {
        /** @var TestNestedDto $dto */
        $dto = $this->dataTransportHelper->createFromArray(
            [
                'id' => 'my-id',
                'test_dto1' => [
                    'param_one' => 'test1-1',
                    'param_two' => 'test1-2',
                    'param_three' => 'test1-3'
                ],
                'test_dto2' => [
                    'param_one' => 'test2-1',
                    'param_two' => 'test2-2',
                    'param_three' => 'test2-3'
                ]
            ],
            TestNestedDto::class
        );

        $this->assertSame('my-id', $dto->getId());

        $this->assertSame('test1-1', $dto->getTestDto1()->getParamOne());
        $this->assertSame('test1-2', $dto->getTestDto1()->getParamTwo());
        $this->assertSame('test1-3', $dto->getTestDto1()->getParamThree());

        $this->assertSame('test2-1', $dto->getTestDto2()->getParamOne());
        $this->assertSame('test2-2', $dto->getTestDto2()->getParamTwo());
        $this->assertSame('test2-3', $dto->getTestDto2()->getParamThree());
    }

    public function testCreateUpdatedDataObject(): void
    {
        /** @var TestDto $dto */
        $dto = $this->dataTransportHelper->createFromArray(
            [
                'param_one' => 'test1',
                'param_two' => 'test2',
                'param_three' => 'test3'
            ],
            TestDto::class
        );

        /** @var TestDto $updatedDto */
        $updatedDto = $this->dataTransportHelper->createUpdatedObjectFromArray(
            $dto,
            [
                'param_two' => 'test4'
            ]
        );

        $this->assertNotSame($dto, $updatedDto);

        $this->assertSame('test2', $dto->getParamTwo());

        $this->assertSame('test1', $updatedDto->getParamOne());
        $this->assertSame('test4', $updatedDto->getParamTwo());
        $this->assertSame('test3', $updatedDto->getParamThree());
    }

    public function testCreateUpdatedNestedDataObject(): void
    {
        /** @var TestNestedDto $dto */
        $dto = $this->dataTransportHelper->createFromArray(
            [
                'id' => 'my-id',
                'test_dto1' => [
                    'param_one' => 'test1-1',
                    'param_two' => 'test1-2',
                    'param_three' => 'test1-3'
                ],
                'test_dto2' => [
                    'param_one' => 'test2-1',
                    'param_two' => 'test2-2',
                    'param_three' => 'test2-3'
                ]
            ],
            TestNestedDto::class
        );

        /** @var TestDto $updatedDto */
        $updatedDto = $this->dataTransportHelper->createUpdatedObjectFromArray(
            $dto,
            [
                'test_dto1' => [
                    'param_two' => 'test3'
                ]
            ]
        );

        $this->assertNotSame($dto, $updatedDto);

        $this->assertSame('test1-2', $dto->getTestDto1()->getParamTwo());

        $this->assertSame('my-id', $updatedDto->getId());

        $this->assertSame('test1-1', $updatedDto->getTestDto1()->getParamOne());
        $this->assertSame('test3', $updatedDto->getTestDto1()->getParamTwo());
        $this->assertSame('test1-3', $updatedDto->getTestDto1()->getParamThree());

        $this->assertSame('test2-1', $updatedDto->getTestDto2()->getParamOne());
        $this->assertSame('test2-2', $updatedDto->getTestDto2()->getParamTwo());
        $this->assertSame('test2-3', $updatedDto->getTestDto2()->getParamThree());
    }

    public function testCreateExtensibleDataObject(): void
    {
        $dto = $this->dataTransportHelper->createFromArray(
            [
                'param_one' => 'test1-1',
                'param_two' => 'test1-2',
                'extension_attributes' => [
                    'additional_value' => 'Hello World!'
                ]
            ],
            TestExtensibleDtoInterface::class
        );

        $this->assertSame('Hello World!', $dto->getExtensionAttributes()->getAdditionalValue());
    }

    public function testCreateMutableDataObject(): void
    {
        /** @var TestMutableDto $dto */
        $dto = $this->dataTransportHelper->createFromArray(
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
        $dto = $this->dataTransportHelper->createFromArray(
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
        $dto = $this->dataTransportHelper->createFromArray(
            [
                'immutable_one' => 'test1',
                'immutable_two' => 'test2',
                'mutable_three' => 'test3'
            ],
            TestHybridDto::class
        );

        $this->assertSame('test1', $dto->getImmutableOne());
        $this->assertSame('test2', $dto->getImmutableTwo());
        $this->assertSame('test3', $dto->getMutableThree());
    }

    /**
     * @return array
     */
    public function hydratingStrategyDataProvider(): array
    {
        return [
            [
                TestDto::class,
                [
                    'param_one' => 'test1',
                    'param_two' => 'test2',
                    'param_three' => 'test3'
                ],
                [
                    DataTransportHelper::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [
                        'param_one' => [
                            'parameter' => 'paramOne',
                            'type' => 'string'
                        ],
                        'param_two' => [
                            'parameter' => 'paramTwo',
                            'type' => 'string'
                        ],
                        'param_three' => [
                            'parameter' => 'paramThree',
                            'type' => 'string'
                        ],
                    ],
                    DataTransportHelper::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
                    DataTransportHelper::HYDRATOR_STRATEGY_SETTER => []
                ]
            ],
            [
                TestSimpleObject::class,
                [
                    'param_one' => 'test1',
                    'param_two' => 'test2',
                    'param_three' => 'test3'
                ],
                [
                    DataTransportHelper::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [],
                    DataTransportHelper::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [
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
                    DataTransportHelper::HYDRATOR_STRATEGY_SETTER => []
                ]
            ],
            [
                TestHybridDto::class,
                [
                    'immutable_one' => 'test1',
                    'immutable_two' => 'test2',
                    'mutable_three' => 'test3'
                ],
                [
                    DataTransportHelper::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [
                        'immutable_one' => [
                            'parameter' => 'immutableOne',
                            'type' => 'string',
                        ],
                        'immutable_two' => [
                            'parameter' => 'immutableTwo',
                            'type' => 'string',
                        ],
                    ],
                    DataTransportHelper::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
                    DataTransportHelper::HYDRATOR_STRATEGY_SETTER => [
                        'mutable_three' => [
                            'method' => 'setMutableThree',
                            'type' => 'string'
                        ]
                    ]
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
        $res = $this->dataTransportHelper->getValuesHydratingStrategy($className, $dataPayload);

        $this->assertEquals($expectedResult, $res);
    }
}
