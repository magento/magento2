<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Catalog\Model\Product\Pricing\Renderer;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\ConfigurableProduct\Plugin\Catalog\Model\Product\Pricing\Renderer\SalableResolver as SalableResolverPlugin;
use Magento\Framework\Pricing\SaleableInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SalableResolverTest
 */
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
    public function testAfterIsSalable($salableItem, bool $isSalable, bool $typeIsSalable, bool $expectedResult)
    {
        $salableResolver = $this->createMock(SalableResolver::class);

        $this->typeConfigurable->method('isSalable')
            ->willReturn($typeIsSalable);

        $result = $this->salableResolver->afterIsSalable($salableResolver, $isSalable, $salableItem);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testAfterIsSalable
     *
     * @return array
     */
    public function afterIsSalableDataProvider(): array
    {
        $simpleSalableItem = $this->createMock(SaleableInterface::class);
        $simpleSalableItem->method('getTypeId')
            ->willReturn('simple');

        $configurableSalableItem = $this->createMock(SaleableInterface::class);
        $configurableSalableItem->method('getTypeId')
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
