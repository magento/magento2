<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Pricing\Price\DiscountCalculator;
use Magento\Bundle\Pricing\Price\DiscountProviderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\PriceInfo\Base;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DiscountCalculatorTest extends TestCase
{
    /**
     * @var DiscountCalculator
     */
    protected $calculator;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Base|MockObject
     */
    protected $priceInfoMock;

    /**
     * @var FinalPrice|MockObject
     */
    protected $finalPriceMock;

    /**
     * @var DiscountProviderInterface|MockObject
     */
    protected $priceMock;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->productMock = $this->createMock(Product::class);
        $this->priceInfoMock = $this->createPartialMock(
            Base::class,
            ['getPrice', 'getPrices']
        );
        $this->finalPriceMock = $this->createMock(FinalPrice::class);
        $this->priceMock = $this->getMockForAbstractClass(
            DiscountProviderInterface::class
        );
        $this->calculator = new DiscountCalculator();
    }

    /**
     * Returns price mock with specified %
     *
     * @param int $value
     * @return MockObject
     */
    protected function getPriceMock($value)
    {
        $price = clone $this->priceMock;
        $price->expects($this->exactly(3))
            ->method('getDiscountPercent')
            ->willReturn($value);
        return $price;
    }

    /**
     * test method calculateDiscount with default price amount
     */
    public function testCalculateDiscountWithDefaultAmount()
    {
        $this->productMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($this->finalPriceMock);
        $this->finalPriceMock->expects($this->once())
            ->method('getValue')
            ->willReturn(100);
        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->willReturn(
                [
                    $this->getPriceMock(30),
                    $this->getPriceMock(20),
                    $this->getPriceMock(40),
                ]
            );
        $this->assertEquals(20, $this->calculator->calculateDiscount($this->productMock));
    }

    /**
     * test method calculateDiscount with custom price amount
     */
    public function testCalculateDiscountWithCustomAmount()
    {
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->willReturn(
                [
                    $this->getPriceMock(30),
                    $this->getPriceMock(20),
                    $this->getPriceMock(40),
                ]
            );
        $this->assertEquals(10, $this->calculator->calculateDiscount($this->productMock, 50));
    }
}
