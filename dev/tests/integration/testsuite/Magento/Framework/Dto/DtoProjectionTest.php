<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Dto\Mock\ConfigureTestDtos;
use Magento\Framework\Dto\Mock\ConfigureTestProjections;
use Magento\Framework\Dto\Mock\ImmutableDtoInterface;
use Magento\Framework\Dto\Mock\ImmutableDtoTwoInterface;
use Magento\Framework\Dto\Mock\ImmutableNestedDtoInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DtoProjectionTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DtoProcessor
     */
    private $dtoProcessor;

    /**
     * @var DtoProjection
     */
    private $dtoProjection;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        ConfigureTestDtos::execute();
        ConfigureTestProjections::execute();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->dtoProcessor = $this->objectManager->get(DtoProcessor::class);
        $this->dtoProjection = $this->objectManager->get(DtoProjection::class);
    }

    public function testShouldCreateProjectedObject(): void
    {
        /** @var ImmutableDtoInterface $dto */
        $dto = $this->dtoProcessor->createFromArray(
            [
                'prop1' => 1,
                'prop2' => 'b',
                'prop3' => ['abc', 'def', 'ghi'],
                'prop4' => [1, 2, 3, 4],
            ],
            ImmutableDtoInterface::class
        );

        /** @var ImmutableDtoTwoInterface $dto2 */
        $dto2 = $this->dtoProjection->execute(
            ImmutableDtoTwoInterface::class,
            ImmutableDtoInterface::class,
            $dto
        );

        self::assertSame($dto->getProp1() + 1, $dto2->getPropOne()); // Managed in pre-processing
        self::assertSame('bz', $dto2->getPropTwo()); // Managed in post-processing
        self::assertSame($dto->getProp3(), $dto2->getPropThree());
        self::assertSame($dto->getProp4(), $dto2->getPropFour());
    }

    public function testShouldCreateNestedProjectedObject(): void
    {
        /** @var ImmutableDtoInterface $dto */
        $dto = $this->dtoProcessor->createFromArray(
            [
                'prop1' => 1,
                'prop2' => 'b',
                'prop3' => ['abc', 'def', 'ghi'],
                'prop4' => [1, 2, 3, 4],
            ],
            ImmutableDtoInterface::class
        );

        /** @var ImmutableNestedDtoInterface $dto2 */
        $dto2 = $this->dtoProjection->execute(
            ImmutableNestedDtoInterface::class,
            ImmutableDtoInterface::class,
            $dto
        );

        self::assertSame($dto->getProp1(), $dto2->getTestDto1()->getProp1());
        self::assertSame($dto->getProp2(), $dto2->getTestDto1()->getProp2());
        self::assertSame($dto->getProp3(), $dto2->getTestDto1()->getProp3());
        self::assertSame($dto->getProp4(), $dto2->getTestDto1()->getProp4());
    }

    public function testShouldCreateFromNestedProjectedObject(): void
    {
        /** @var ImmutableNestedDtoInterface $dto */
        $dto = $this->dtoProcessor->createFromArray(
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
                'test_dto_array' => []
            ],
            ImmutableNestedDtoInterface::class
        );

        /** @var ImmutableDtoInterface $dto2 */
        $dto2 = $this->dtoProjection->execute(
            ImmutableDtoInterface::class,
            ImmutableNestedDtoInterface::class,
            $dto
        );

        self::assertSame($dto->getTestDto1()->getProp1(), $dto2->getProp1());
        self::assertSame($dto->getTestDto1()->getProp2(), $dto2->getProp2());
        self::assertSame($dto->getTestDto1()->getProp3(), $dto2->getProp3());
        self::assertSame($dto->getTestDto1()->getProp4(), $dto2->getProp4());
    }
}
