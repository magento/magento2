<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\PriceInfoInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegularPriceTest extends TestCase
{
    /**
     * @var RegularPrice
     */
    protected $regularPrice;

    /**
     * @var PriceInfoInterface|MockObject
     */
    protected $priceInfoMock;

    /**
     * @var Product|MockObject
     */
    protected $saleableItemMock;

    /**
     * @var Calculator|MockObject
     */
    protected $calculatorMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $qty = 1;
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->priceInfoMock = $this->createMock(Base::class);
        $this->calculatorMock = $this->createMock(Calculator::class);

        $this->saleableItemMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);

        $this->regularPrice = new RegularPrice(
            $this->saleableItemMock,
            $qty,
            $this->calculatorMock,
            $this->priceCurrencyMock
        );
    }

    /**
     * Test method testGetValue
     *
     * @param float|bool $price
     * @dataProvider testGetValueDataProvider
     */
    public function testGetValue($price)
    {
        $convertedPrice = 85;
        $this->saleableItemMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($price);
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($price)
            ->willReturn($convertedPrice);
        $this->assertEquals($convertedPrice, $this->regularPrice->getValue());
        //The second call will use cached value
        $this->assertEquals($convertedPrice, $this->regularPrice->getValue());
    }

    /**
     * Data provider for testGetValue
     *
     * @return array
     */
    public static function testGetValueDataProvider()
    {
        return [
            'With price' => [100.00],
            'Without price' => [false]
        ];
    }

    /**
     * Test method testGetDisplayValue
     */
    public function testGetAmount()
    {
        $priceValue = 77;
        $convertedPrice = 56.32;
        $amountValue = 88;

        $this->saleableItemMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($priceValue);
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($priceValue)
            ->willReturn($convertedPrice);
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($convertedPrice)
            ->willReturn($amountValue);
        $this->assertEquals($amountValue, $this->regularPrice->getAmount());
    }

    /**
     * Test method getPriceType
     */
    public function testGetPriceCode()
    {
        $this->assertEquals(RegularPrice::PRICE_CODE, $this->regularPrice->getPriceCode());
    }
}
