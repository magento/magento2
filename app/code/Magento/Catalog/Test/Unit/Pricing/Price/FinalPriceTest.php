<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Pricing\Price;

/**
 * Final Price test
 */
class FinalPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Price\FinalPrice
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\BasePrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $basePriceMock;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Set up function
     */
    protected function setUp()
    {
        $this->saleableMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->priceInfoMock = $this->basePriceMock = $this->createMock(
            \Magento\Framework\Pricing\PriceInfo\Base::class
        );
        $this->basePriceMock = $this->createMock(\Magento\Catalog\Pricing\Price\BasePrice::class);

        $this->calculatorMock = $this->createMock(\Magento\Framework\Pricing\Adjustment\Calculator::class);

        $this->saleableMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo(\Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE))
            ->will($this->returnValue($this->basePriceMock));
        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->model = new \Magento\Catalog\Pricing\Price\FinalPrice(
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
