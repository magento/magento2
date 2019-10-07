<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Dto\Mock\ConfigureTestDtos;
use Magento\Framework\Dto\Mock\ImmutableDto;
use Magento\Framework\Dto\Mock\ImmutableDtoFactory;
use Magento\Framework\Dto\Mock\ImmutableDtoTwo;
use Magento\Framework\Dto\Mock\ImmutableDtoTwoFactory;
use Magento\Framework\Dto\Mock\ImmutableNestedDto;
use Magento\Framework\Dto\Mock\ImmutableNestedDtoFactory;
use Magento\Framework\Dto\Mock\MutableDto;
use Magento\Framework\Dto\Mock\MutableDtoFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DtoCreationViaFactoryTest extends TestCase
{
    /**
     * @var ImmutableNestedDtoFactory
     */
    private $immutableNestedDtoFactory;

    /**
     * @var MutableDtoFactory
     */
    private $mutableDtoFactory;

    /**
     * @var ImmutableDtoFactory
     */
    private $immutableDtoFactory;

    /**
     * @var ImmutableDtoTwoFactory
     */
    private $immutableDtoTwoFactory;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        ConfigureTestDtos::execute();

        $objectManager = Bootstrap::getObjectManager();
        $this->mutableDtoFactory = $objectManager->get(MutableDtoFactory::class);
        $this->immutableNestedDtoFactory = $objectManager->get(ImmutableNestedDtoFactory::class);
        $this->immutableDtoFactory = $objectManager->get(ImmutableDtoFactory::class);
        $this->immutableDtoTwoFactory = $objectManager->get(ImmutableDtoTwoFactory::class);
    }

    public function testCreateImmutableDtoFromArray(): void
    {
        /** @var ImmutableDto $dto */
        $dto = $this->immutableDtoFactory->create(
            [
                'prop1' => 1,
                'prop2' => 'b',
                'prop3' => ['abc', 'def', 'ghi'],
                'prop4' => [1, 2, 3, 4],
            ]
        );

        $this->assertSame(1, $dto->getProp1());
        $this->assertSame('b', $dto->getProp2());
        $this->assertSame(['abc', 'def', 'ghi'], $dto->getProp3());
        $this->assertSame([1, 2, 3, 4], $dto->getProp4());
    }

    public function testCreateWithCamelCaseParameters(): void
    {
        /** @var ImmutableDtoTwo $dto */
        $dto = $this->immutableDtoTwoFactory->create(
            [
                'propOne' => 1,
                'propTwo' => 'b',
                'propThree' => ['abc', 'def', 'ghi'],
                'propFour' => [1, 2, 3, 4],
            ]
        );

        $this->assertSame(1, $dto->getPropOne());
        $this->assertSame('b', $dto->getPropTwo());
        $this->assertSame(['abc', 'def', 'ghi'], $dto->getPropThree());
        $this->assertSame([1, 2, 3, 4], $dto->getPropFour());
    }

    public function testCreateWithSnakeCaseParameters(): void
    {
        /** @var ImmutableDtoTwo $dto */
        $dto = $this->immutableDtoTwoFactory->create(
            [
                'prop_one' => 1,
                'prop_two' => 'b',
                'prop_three' => ['abc', 'def', 'ghi'],
                'prop_four' => [1, 2, 3, 4],
            ]
        );

        $this->assertSame(1, $dto->getPropOne());
        $this->assertSame('b', $dto->getPropTwo());
        $this->assertSame(['abc', 'def', 'ghi'], $dto->getPropThree());
        $this->assertSame([1, 2, 3, 4], $dto->getPropFour());
    }

    public function testCreateImmutableDtoWithArraysFromArray(): void
    {
        /** @var ImmutableNestedDto $dto */
        $dto = $this->immutableNestedDtoFactory->create(
            [
                'id' => 'my-id',
                'test_dto1' => [
                    'prop1' => 1,
                    'prop2' => 'b',
                    'prop3' => ['abc', 'def', 'ghi'],
                    'prop4' => [1, 2, 3, 4],
                ],
                'test_dto2' => [
                    'prop1' => 2,
                    'prop2' => 'f',
                    'prop3' => ['jkl', 'mno', 'pqr'],
                    'prop4' => [5, 6, 7, 8],
                ],
                'test_dto_array' => [
                    [
                        'prop1' => 3,
                        'prop2' => '2',
                        'prop3' => ['123', '456', '789'],
                        'prop4' => [9, 10, 11, 12],
                    ],
                    [
                        'prop1' => 4,
                        'prop2' => '6',
                        'prop3' => ['012', '034', '056'],
                        'prop4' => [13, 14, 15, 16],
                    ]
                ]
            ]
        );

        $this->assertSame('my-id', $dto->getId());

        $this->assertSame(1, $dto->getTestDto1()->getProp1());
        $this->assertSame('b', $dto->getTestDto1()->getProp2());
        $this->assertSame(['abc', 'def', 'ghi'], $dto->getTestDto1()->getProp3());
        $this->assertSame([1, 2, 3, 4], $dto->getTestDto1()->getProp4());

        $this->assertSame(2, $dto->getTestDto2()->getProp1());
        $this->assertSame('f', $dto->getTestDto2()->getProp2());
        $this->assertSame(['jkl', 'mno', 'pqr'], $dto->getTestDto2()->getProp3());
        $this->assertSame([5, 6, 7, 8], $dto->getTestDto2()->getProp4());

        $this->assertCount(2, $dto->getTestDtoArray());

        $this->assertSame(3, $dto->getTestDtoArray()[0]->getProp1());
        $this->assertSame('2', $dto->getTestDtoArray()[0]->getProp2());
        $this->assertSame(['123', '456', '789'], $dto->getTestDtoArray()[0]->getProp3());
        $this->assertSame([9, 10, 11, 12], $dto->getTestDtoArray()[0]->getProp4());

        $this->assertSame(4, $dto->getTestDtoArray()[1]->getProp1());
        $this->assertSame('6', $dto->getTestDtoArray()[1]->getProp2());
        $this->assertSame(['012', '034', '056'], $dto->getTestDtoArray()[1]->getProp3());
        $this->assertSame([13, 14, 15, 16], $dto->getTestDtoArray()[1]->getProp4());
    }

    public function testCreateMutableDataObject(): void
    {
        /** @var MutableDto $dto */
        $dto = $this->mutableDtoFactory->create(
            [
                'prop1' => 1,
                'prop3' => ['abc', 'def', 'ghi'],
            ]
        );

        $this->assertSame(1, $dto->getProp1());
        $this->assertSame(['abc', 'def', 'ghi'], $dto->getProp3());
    }

    public function testRaiseInjectionWithInvalidParameters(): void
    {
        $this->expectExceptionMessage(
            'Cannot inject property "some_weird_parameter" in class "Magento\Framework\Dto\Mock\MutableDto".'
        );

        /** @var MutableDto $dto */
        $this->mutableDtoFactory->create(
            [
                'prop1' => 1,
                'prop2' => 'b',
                'prop3' => ['abc', 'def', 'ghi'],
                'some_weird_parameter' => 9
            ]
        );
    }

    public function testRaiseExceptionWithInvalidParametersType(): void
    {
        $this->expectExceptionMessage(
            'Error occurred during "prop1" processing. '
            . 'The "invalid_format" value\'s type is invalid. The "int" type was expected. Verify and try again.'
        );

        /** @var MutableDto $dto */
        $this->mutableDtoFactory->create(
            [
                'prop1' => 'invalid_format',
                'prop2' => 'b',
                'prop3' => ['abc', 'def', 'ghi']
            ]
        );
    }
}
