<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Unit\Pricing\Price;

/**
 * Class LinkPriceTest
 */
class LinkPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Pricing\Price\LinkPrice
     */
    protected $linkPrice;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkMock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Test setUp
     */
    protected function setUp()
    {
        $this->saleableItemMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->amountMock = $this->getMock('Magento\Framework\Pricing\Amount\Base', [], [], '', false);
        $this->calculatorMock = $this->getMock('Magento\Framework\Pricing\Adjustment\Calculator', [], [], '', false);
        $this->linkMock = $this->getMock(
            'Magento\Downloadable\Model\Link',
            ['getPrice', 'getProduct', '__wakeup'],
            [],
            '',
            false
        );

        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');

        $this->linkPrice = new \Magento\Downloadable\Pricing\Price\LinkPrice(
            $this->saleableItemMock,
            1,
            $this->calculatorMock,
            $this->priceCurrencyMock
        );
    }

    public function testGetLinkAmount()
    {
        $amount = 100;
        $convertedAmount = 50;

        $this->linkMock->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue($amount));
        $this->linkMock->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($this->saleableItemMock));
        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($amount)
            ->will($this->returnValue($convertedAmount));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($convertedAmount, $this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($convertedAmount));

        $result = $this->linkPrice->getLinkAmount($this->linkMock);
        $this->assertEquals($convertedAmount, $result);
    }
}
