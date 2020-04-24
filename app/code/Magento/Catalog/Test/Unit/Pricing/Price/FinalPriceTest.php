<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FinalPriceTest extends TestCase
{
    /**
     * @var FinalPrice
     */
    protected $model;

    /**
     * @var PriceInfoInterface|MockObject
     */
    protected $priceInfoMock;

    /**
     * @var BasePrice|MockObject
     */
    protected $basePriceMock;

    /**
     * @var SaleableInterface|MockObject
     */
    protected $saleableMock;

    /**
     * @var Calculator|MockObject
     */
    protected $calculatorMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Set up function
     */
    protected function setUp(): void
    {
        $this->saleableMock = $this->createMock(Product::class);
        $this->priceInfoMock = $this->basePriceMock = $this->createMock(
            Base::class
        );
        $this->basePriceMock = $this->createMock(BasePrice::class);

        $this->calculatorMock = $this->createMock(Calculator::class);

        $this->saleableMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo(BasePrice::PRICE_CODE))
            ->will($this->returnValue($this->basePriceMock));
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $this->model = new FinalPrice(
            $this->saleableMock,
            1,
            $this->calculatorMock,
            $this->priceCurrencyMock
        );
    }

    /**
     * test for getValue
     */
    public function testGetValue()
    {
        $price = 10;
        $this->basePriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($price));
        $result = $this->model->getValue();
        $this->assertEquals($price, $result);
    }

    /**
     * Test getMinimalPrice() when product->getMinimalPrice returns null
     */
    public function testGetMinimalPriceWithoutMinimalPrice()
    {
        $basePrice = 10;
        $minimalPrice = 5;
        $this->basePriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($basePrice));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->equalTo($basePrice))
            ->will($this->returnValue($minimalPrice));
        $this->saleableMock->expects($this->once())
            ->method('getMinimalPrice')
            ->will($this->returnValue(null));
        $result = $this->model->getMinimalPrice();
        $this->assertEquals($minimalPrice, $result);
        //The second time will return cached value
        $result = $this->model->getMinimalPrice();
        $this->assertEquals($minimalPrice, $result);
    }

    /**
     * Test getMinimalPrice()
     */
    public function testGetMinimalPriceWithMinimalPrice()
    {
        $minimalPrice = 5.234;
        $convertedPrice = 3.98;
        $finalPrice = 3.89;

        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($minimalPrice)
            ->will($this->returnValue($convertedPrice));
        $this->basePriceMock->expects($this->never())
            ->method('getValue');
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->equalTo($convertedPrice))
            ->will($this->returnValue($finalPrice));
        $this->saleableMock->expects($this->once())
            ->method('getMinimalPrice')
            ->will($this->returnValue($minimalPrice));
        $result = $this->model->getMinimalPrice();
        $this->assertEquals($finalPrice, $result);
        //The second time will return cached value
        $result = $this->model->getMinimalPrice();
        $this->assertEquals($finalPrice, $result);
    }

    /**
     * Test getMaximalPrice()
     */
    public function testGetMaximalPrice()
    {
        $basePrice = 10;
        $minimalPrice = 5;
        $this->basePriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($basePrice));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->equalTo($basePrice))
            ->will($this->returnValue($minimalPrice));
        $result = $this->model->getMaximalPrice();
        $this->assertEquals($minimalPrice, $result);
        //The second time will return cached value
        $result = $this->model->getMaximalPrice();
        $this->assertEquals($minimalPrice, $result);
    }
}
