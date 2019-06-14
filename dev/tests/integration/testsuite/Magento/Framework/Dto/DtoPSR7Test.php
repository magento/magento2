<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Dto\Mock\ImmutableDtoTwo;
use Magento\Framework\Dto\Mock\MockDtoConfig;
use Magento\Framework\Dto\Mock\MutableDto;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DtoPSR7Test extends TestCase
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
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                DtoConfig::class => MockDtoConfig::class
            ]
        ]);

        $this->objectManager = Bootstrap::getObjectManager();
        $this->dataProcessor = $this->objectManager->get(DtoProcessor::class);
    }

    public function testShouldSupportPSR7(): void
    {
        /** @var ImmutableDtoTwo $dto */
        $dto = $this->objectManager->create(
            ImmutableDtoTwo::class, [
                'propOne' => 1,
                'propTwo' => 'b',
                'propThree' => ['abc', 'def', 'ghi'],
                'propFour' => [1, 2, 3, 4],
            ],
            ImmutableDtoTwo::class
        );

        $dto = $dto
            ->withPropOne(2)
            ->withPropTwo('c');

        self::assertSame(2, $dto->getPropOne());
        self::assertSame('c', $dto->getPropTwo());

        self::assertSame(['abc', 'def', 'ghi'], $dto->getPropThree());
        self::assertSame([1, 2, 3, 4], $dto->getPropFour());
    }

    public function testShouldNotUsePSR7OnMutableDtos(): void
    {
        /** @var MutableDto $dto */
        $dto = $this->dataProcessor->createFromArray(
            [
                'prop1' => 1,
                'prop3' => ['abc', 'def', 'ghi'],
            ],
            MutableDto::class
        );

        self::assertFalse(method_exists($dto, 'withProp1'));
        self::assertFalse(method_exists($dto, 'withProp2'));
    }
}
