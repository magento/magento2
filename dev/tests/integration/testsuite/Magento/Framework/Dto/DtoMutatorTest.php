<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Dto\Mock\ImmutableDto;
use Magento\Framework\Dto\Mock\ImmutableDtoMutator;
use Magento\Framework\Dto\Mock\ImmutableNestedDto;
use Magento\Framework\Dto\Mock\ImmutableNestedDtoMutator;
use Magento\Framework\Dto\Mock\MockDtoConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DtoMutatorTest extends TestCase
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
     * @inheritDoc
     */
    protected function setUp()
    {
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                DtoConfig::class => MockDtoConfig::class
            ]
        ]);

        $this->objectManager = Bootstrap::getObjectManager();
        $this->dtoProcessor = $this->objectManager->get(DtoProcessor::class);
    }

    public function testDtoMutator(): void
    {
        /** @var ImmutableDto $dto */
        $dto = $this->objectManager->create(ImmutableDto::class, [
            'prop1' => 1,
            'prop2' => 'b',
            'prop3' => ['abc', 'def', 'ghi'],
            'prop4' => [1, 2, 3, 4],
        ]);

        /** @var ImmutableDtoMutator $immutableDtoMutator */
        $immutableDtoMutator = $this->objectManager->create(ImmutableDtoMutator::class);

        $dto = $immutableDtoMutator
            ->withProp1(2)
            ->withProp2('y')
            ->withProp3(['zyx', '123'])
            ->mutate($dto);

        $this->assertSame(2, $dto->getProp1());
        $this->assertSame('y', $dto->getProp2());
        $this->assertSame(['zyx', '123'], $dto->getProp3());
        $this->assertSame([1, 2, 3, 4], $dto->getProp4());
    }

    public function testNestedDto(): void
    {
        /** @var ImmutableNestedDto $dto */
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
            ],
            ImmutableNestedDto::class
        );

        /** @var ImmutableNestedDtoMutator $dtoMutator */
        $dtoMutator = $this->objectManager->create(ImmutableNestedDtoMutator::class);

        $dto = $dtoMutator
            ->withId('my-new-id')
            ->withTestDto1(
                $this->objectManager->create(ImmutableDto::class, [
                    'prop1' => 5,
                    'prop2' => 'b1',
                    'prop3' => ['abc1', 'def1', 'ghi1'],
                    'prop4' => [113, 114, 115, 116],
                ])
            )
            ->withTestDtoArray([
                $this->objectManager->create(ImmutableDto::class, [
                    'prop1' => 6,
                    'prop2' => 'b2',
                    'prop3' => ['abc2', 'def2', 'ghi2'],
                    'prop4' => [213, 214, 215, 216],
                ])
            ])
            ->mutate($dto);

        $this->assertSame('my-new-id', $dto->getId());
        $this->assertSame(5, $dto->getTestDto1()->getProp1());
        $this->assertSame('b1', $dto->getTestDto1()->getProp2());
        $this->assertSame(['abc1', 'def1', 'ghi1'], $dto->getTestDto1()->getProp3());
        $this->assertSame([113, 114, 115, 116], $dto->getTestDto1()->getProp4());

        $this->assertCount(1, $dto->getTestDtoArray());
        $this->assertSame(6, $dto->getTestDtoArray()[0]->getProp1());
        $this->assertSame('b2', $dto->getTestDtoArray()[0]->getProp2());
        $this->assertSame(['abc2', 'def2', 'ghi2'], $dto->getTestDtoArray()[0]->getProp3());
        $this->assertSame([213, 214, 215, 216], $dto->getTestDtoArray()[0]->getProp4());
    }
}
