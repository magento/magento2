<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Pricing\Price;

use Magento\Framework\Pricing\PriceInfoInterface;

/**
 * Class MsrpPriceTest
 */
class MsrpPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Msrp\Pricing\Price\MsrpPrice
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItem;

    /**
     * @var \Magento\Catalog\Pricing\Price\BasePrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $price;
    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfo;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculator;

    /**
     * @var \Magento\Msrp\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp()
    {
        $this->saleableItem = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getPriceInfo', '__wakeup'],
            [],
            '',
            false
        );

        $this->priceInfo = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->price = $this->getMock('Magento\Catalog\Pricing\Price\BasePrice', [], [], '', false);

        $this->priceInfo->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue([]));

        $this->saleableItem->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfo));

        $this->priceInfo->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo('base_price'))
            ->will($this->returnValue($this->price));

        $this->calculator = $this->getMockBuilder('Magento\Framework\Pricing\Adjustment\Calculator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->getMock(
            'Magento\Msrp\Helper\Data',
            ['isShowPriceOnGesture', 'getMsrpPriceMessage', 'canApplyMsrp'],
            [],
            '',
            false
        );
        $this->config = $this->getMock('Magento\Msrp\Model\Config', ['isEnabled'], [], '', false);

        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');

        $this->object = new MsrpPrice(
            $this->saleableItem,
            PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->helper,
            $this->config
        );
    }

    public function testIsShowPriceOnGestureTrue()
    {
        $this->helper->expects($this->once())
            ->method('isShowPriceOnGesture')
            ->with($this->equalTo($this->saleableItem))
            ->will($this->returnValue(true));

        $this->assertTrue($this->object->isShowPriceOnGesture());
    }

    public function testIsShowPriceOnGestureFalse()
    {
        $this->helper->expects($this->once())
            ->method('isShowPriceOnGesture')
            ->with($this->equalTo($this->saleableItem))
            ->will($this->returnValue(false));

        $this->assertFalse($this->object->isShowPriceOnGesture());
    }

    public function testGetMsrpPriceMessage()
    {
        $expectedMessage = 'test';
        $this->helper->expects($this->once())
            ->method('getMsrpPriceMessage')
            ->with($this->equalTo($this->saleableItem))
            ->will($this->returnValue($expectedMessage));

        $this->assertEquals($expectedMessage, $this->object->getMsrpPriceMessage());
    }

    public function testIsMsrpEnabled()
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        $this->assertTrue($this->object->isMsrpEnabled());
    }

    public function testCanApplyMsrp()
    {
        $this->helper->expects($this->once())
            ->method('canApplyMsrp')
            ->with($this->equalTo($this->saleableItem))
            ->will($this->returnValue(true));

        $this->assertTrue($this->object->canApplyMsrp($this->saleableItem));
    }
}
