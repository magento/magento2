<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Pricing\Price\DiscountCalculator;
use Magento\Bundle\Pricing\Price\DiscountProviderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\Price\PriceInterface;
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
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->productMock = $this->createMock(Product::class);
        $this->priceInfoMock =  $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPrice', 'getPrices'])
            ->addMethods(['getValue'])
            ->getMock();
        $this->finalPriceMock = $this->createMock(FinalPrice::class);
        $this->priceMock = $this->getMockForAbstractClass(
            DiscountProviderInterface::class
        );
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['roundPrice'])
            ->getMockForAbstractClass();
        $this->calculator = new DiscountCalculator($this->priceCurrencyMock);
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
        $this->priceCurrencyMock->expects($this->once())
            ->method('roundPrice')
            ->willReturn(20);
        $this->assertEquals(20, $this->calculator->calculateDiscount($this->productMock));
    }

    /**
     * test method calculateDiscount with custom price amount
     *
     * @dataProvider providerForWithDifferentAmount
     */
    public function testCalculateDiscountWithCustomAmount(mixed $discount, mixed $value, float $expectedResult)
    {
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
        $this->priceInfoMock->expects($this->any())
            ->method('getPrices')
            ->willReturn([$this->getPriceMock($discount)]);
        if ($value === null) {
            $abstractPriceMock = $this->getMockForAbstractClass(
                PriceInterface::class
            );
            $this->priceInfoMock->expects($this->any())
                ->method('getPrice')
                ->willReturn($abstractPriceMock);
            $abstractPriceMock->expects($this->any())
                ->method('getValue')
                ->willReturn($expectedResult);
        }
        $this->priceCurrencyMock->expects($this->any())
            ->method('roundPrice')
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->calculator->calculateDiscount($this->productMock, $value));
    }

    /**
     * @return array
     */
    public static function providerForWithDifferentAmount()
    {
        return [
            'test case 1 with discount amount' => [20, 50, 10],
            'test case 2 for null discount amount' => [null, 30, 30],
            'test case 3 with discount amount' => [99, 5.5, 5.45],
            'test case 4 with null value' => [50, null, 50]
        ];
    }
}
