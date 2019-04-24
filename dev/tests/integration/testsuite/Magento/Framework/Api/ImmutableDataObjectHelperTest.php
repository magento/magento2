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
use Magento\Framework\Api\TestDtoClasses\TestNestedDto;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ImmutableDataObjectHelperTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ImmutableDataObjectHelper
     */
    private $immutableDataObjectHelper;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->immutableDataObjectHelper = $this->objectManager->get(ImmutableDataObjectHelper::class);

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
            $this->immutableDataObjectHelper->getObjectData($dto)
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
            $this->immutableDataObjectHelper->getObjectData($nestedDto)
        );
    }

    public function testCreateImmutableDtoFromArray(): void
    {
        /** @var TestDto $dto */
        $dto = $this->immutableDataObjectHelper->createFromArray(
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
        $dto = $this->immutableDataObjectHelper->createFromArray(
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
        $dto = $this->immutableDataObjectHelper->createFromArray(
            [
                'param_one' => 'test1',
                'param_two' => 'test2',
                'param_three' => 'test3'
            ],
            TestDto::class
        );

        /** @var TestDto $updatedDto */
        $updatedDto = $this->immutableDataObjectHelper->createUpdatedObjectFromArray(
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
        $dto = $this->immutableDataObjectHelper->createFromArray(
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
        $updatedDto = $this->immutableDataObjectHelper->createUpdatedObjectFromArray(
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
        $dto = $this->immutableDataObjectHelper->createFromArray(
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
}
