<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

use Magento\Framework\Api\TestDtoClasses\TestDto;
use Magento\Framework\Api\TestDtoClasses\TestDtoMutator;
use Magento\Framework\Api\TestDtoClasses\TestNestedDto;
use Magento\Framework\Api\TestDtoClasses\TestNestedDtoMutator;
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
        $this->objectManager = Bootstrap::getObjectManager();
        $this->dtoProcessor = $this->objectManager->get(DtoProcessor::class);
    }

    public function testDtoMutator(): void
    {
        /** @var TestDto $dto */
        $dto = $this->objectManager->create(TestDto::class, [
            'paramOne' => 1,
            'paramTwo' => 2.0,
            'paramThree' => 'test3'
        ]);

        /** @var TestDtoMutator $dtoMutator */
        $dtoMutator = $this->objectManager->create(TestDtoMutator::class);

        $dto = $dtoMutator
            ->withParamOne(2)
            ->withParamThree('test123')
            ->mutate($dto);

        $this->assertSame(2, $dto->getParamOne());
        $this->assertSame(2.0, $dto->getParamTwo());
        $this->assertSame('test123', $dto->getParamThree());
    }

    public function testDtoMutatorWithArrays(): void
    {
        /** @var TestNestedDto $dto */
        $dto = $this->dtoProcessor->createFromArray(
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

        /** @var TestNestedDtoMutator $dtoMutator */
        $dtoMutator = $this->objectManager->create(TestNestedDtoMutator::class);

        $dto = $dtoMutator
            ->withId('my-new-id')
            ->withTestDto1(
                $this->objectManager->create(TestDto::class, [
                    'paramOne' => 10,
                    'paramTwo' => 20.0,
                    'paramThree' => 'test10'
                ])
            )
            ->withTestDtoArray([
                $this->objectManager->create(TestDto::class, [
                    'paramOne' => 20,
                    'paramTwo' => 30.0,
                    'paramThree' => 'test20'
                ])
            ])
            ->mutate($dto);

        $this->assertSame('my-new-id', $dto->getId());
        $this->assertSame(10, $dto->getTestDto1()->getParamOne());
        $this->assertSame(20.0, $dto->getTestDto1()->getParamTwo());
        $this->assertSame('test10', $dto->getTestDto1()->getParamThree());

        $this->assertCount(1, $dto->getTestDtoArray());
        $this->assertSame(20, $dto->getTestDtoArray()[0]->getParamOne());
        $this->assertSame(30.0, $dto->getTestDtoArray()[0]->getParamTwo());
        $this->assertSame('test20', $dto->getTestDtoArray()[0]->getParamThree());
    }
}
