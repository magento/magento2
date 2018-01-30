<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Test\Unit\Price;

use \Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Class RegularPriceTest
 */
class AbstractPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractPrice
     */
    protected $price;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountMock;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator |\PHPUnit_Framework_MockObject_MockObject
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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');

        $this->price = $objectManager->getObject(
            'Magento\Framework\Pricing\Test\Unit\Price\Stub',
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => $qty,
                'calculator' => $this->calculatorMock,
                'priceCurrency' => $this->priceCurrencyMock,
            ]
        );
    }

    /**
     * Test method testGetDisplayValue
     */
    public function testGetAmount()
    {
        $priceValue = $this->price->getValue();
        $amountValue = 88;
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->equalTo($priceValue))
            ->will($this->returnValue($amountValue));
        $this->assertEquals($amountValue, $this->price->getAmount());
    }

    /**
     * Test method getPriceType
     */
    public function testGetPriceCode()
    {
        $this->assertEquals(AbstractPrice::PRICE_CODE, $this->price->getPriceCode());
    }

    public function testGetCustomAmount()
    {
        $exclude = false;
        $amount = 21.0;
        $convertedValue = 30.25;
        $customAmount = 42.0;

        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($amount)
            ->will($this->returnValue($convertedValue));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($convertedValue, $this->saleableItemMock, $exclude)
            ->will($this->returnValue($customAmount));

        $this->assertEquals($customAmount, $this->price->getCustomAmount($amount, $exclude));
    }

    public function testGetCustomAmountDefault()
    {
        $customAmount = 42.0;
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->price->getValue(), $this->saleableItemMock, null)
            ->will($this->returnValue($customAmount));

        $this->assertEquals($customAmount, $this->price->getCustomAmount());
    }

    public function testGetQuantity()
    {
        $this->assertEquals(1, $this->price->getQuantity());
    }
}
