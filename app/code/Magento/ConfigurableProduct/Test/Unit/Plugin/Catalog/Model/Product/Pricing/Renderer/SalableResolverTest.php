<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Catalog\Model\Product\Pricing\Renderer;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\ConfigurableProduct\Plugin\Catalog\Model\Product\Pricing\Renderer\SalableResolver as SalableResolverPlugin;
use Magento\Framework\Pricing\SaleableInterface;
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

    protected function setUp()
    {
        $this->typeConfigurable = $this->createMock(TypeConfigurable::class);
        $this->salableResolver = new SalableResolverPlugin($this->typeConfigurable);
    }

    /**
     * @param SaleableInterface|MockObject $salableItem
     * @param bool $isSalable
     * @param bool $typeIsSalable
     * @param bool $expectedResult
     * @return void
     * @dataProvider afterIsSalableDataProvider
     */
    public function testAfterIsSalable($salableItem, bool $isSalable, bool $typeIsSalable, bool $expectedResult): void
    {
        $salableResolver = $this->createMock(SalableResolver::class);

        $this->typeConfigurable->method('isSalable')
            ->willReturn($typeIsSalable);

        $result = $this->salableResolver->afterIsSalable($salableResolver, $isSalable, $salableItem);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function afterIsSalableDataProvider(): array
    {
        $simpleSalableItem = $this->createMock(SaleableInterface::class);
        $simpleSalableItem->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $configurableSalableItem = $this->createMock(SaleableInterface::class);
        $configurableSalableItem->expects($this->once())
            ->method('getTypeId')
            ->willReturn('configurable');

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
