<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Catalog\Model\Product\Pricing\Renderer;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\ConfigurableProduct\Plugin\Catalog\Model\Product\Pricing\Renderer\SalableResolver as SalableResolverPlugin;
use Magento\Framework\Pricing\SaleableInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SalableResolverTest extends TestCase
{
    /**
     * @var TypeConfigurable|MockObject
     */
    private $typeConfigurable;

    /**
     * @var SalableResolverPlugin
     */
    private $salableResolver;

    protected function setUp(): void
    {
        $this->typeConfigurable = $this->createMock(TypeConfigurable::class);
        $this->salableResolver = new SalableResolverPlugin($this->typeConfigurable);
    }

    /**
     * @param Closure $salableItem
     * @param bool $isSalable
     * @param bool $typeIsSalable
     * @param bool $expectedResult
     * @return void
     * @throws Exception
     */
    #[DataProvider('afterIsSalableDataProvider')]
    public function testAfterIsSalable(
        Closure $salableItem,
        bool $isSalable,
        bool $typeIsSalable,
        bool $expectedResult
    ): void {
        $salableItem = $salableItem($this);
        $salableResolver = $this->createMock(SalableResolver::class);

        $this->typeConfigurable->method('isSalable')
            ->willReturn($typeIsSalable);

        $result = $this->salableResolver->afterIsSalable($salableResolver, $isSalable, $salableItem);
        $this->assertEquals($expectedResult, $result);
    }

    protected function getMockForSalableInterface($type)
    {
        $salableItem = $this->createMock(SaleableInterface::class);
        $salableItem->expects($this->once())
            ->method('getTypeId')
            ->willReturn($type);
        return $salableItem;
    }
    /**
     * @return array
     */
    public static function afterIsSalableDataProvider(): array
    {
        $simpleSalableItem = static fn (self $testCase) => $testCase->getMockForSalableInterface('simple');

        $configurableSalableItem = static fn (self $testCase) => $testCase->getMockForSalableInterface('configurable');

        return [
            [
                $simpleSalableItem,
                true,
                false,
                true,
            ],
            [
                $configurableSalableItem,
                true,
                false,
                false,
            ],
        ];
    }
}
