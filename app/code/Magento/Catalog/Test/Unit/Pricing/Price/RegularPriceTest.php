<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use \Magento\Catalog\Pricing\Price\RegularPrice;

/**
 * Class RegularPriceTest
 */
class RegularPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Price\RegularPrice
     */
    protected $regularPrice;

    /**
     * @var \Magento\Framework\Pricing\PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Test setUp
     */
    protected function setUp()
    {
        $qty = 1;
        $this->saleableItemMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->amountMock = $this->getMock('Magento\Framework\Pricing\Amount', [], [], '', false);
        $this->calculatorMock = $this->getMock('Magento\Framework\Pricing\Adjustment\Calculator', [], [], '', false);

        $this->saleableItemMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));

        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');

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
            ->will($this->returnValue($price));
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($price)
            ->will($this->returnValue($convertedPrice));
        $this->assertEquals($convertedPrice, $this->regularPrice->getValue());
        //The second call will use cached value
        $this->assertEquals($convertedPrice, $this->regularPrice->getValue());
    }

    /**
     * Data provider for testGetValue
     *
     * @return array
     */
    public function testGetValueDataProvider()
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
            ->will($this->returnValue($priceValue));
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($priceValue)
            ->will($this->returnValue($convertedPrice));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->equalTo($convertedPrice))
            ->will($this->returnValue($amountValue));
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
