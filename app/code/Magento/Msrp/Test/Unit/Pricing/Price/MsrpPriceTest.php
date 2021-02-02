<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Unit\Pricing\Price;

use Magento\Framework\Pricing\PriceInfoInterface;

/**
 * Class MsrpPriceTest
 */
class MsrpPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Msrp\Pricing\Price\MsrpPrice
     */
    protected $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $saleableItem;

    /**
     * @var \Magento\Catalog\Pricing\Price\BasePrice|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $price;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceInfo;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $calculator;

    /**
     * @var \Magento\Msrp\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->saleableItem = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getPriceInfo', '__wakeup']
        );

        $this->priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $this->price = $this->createMock(\Magento\Catalog\Pricing\Price\BasePrice::class);

        $this->priceInfo->expects($this->any())
            ->method('getAdjustments')
            ->willReturn([]);

        $this->saleableItem->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfo);

        $this->priceInfo->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo('base_price'))
            ->willReturn($this->price);

        $this->calculator = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\Calculator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->createPartialMock(
            \Magento\Msrp\Helper\Data::class,
            ['isShowPriceOnGesture', 'getMsrpPriceMessage', 'canApplyMsrp']
        );
        $this->config = $this->createPartialMock(\Magento\Msrp\Model\Config::class, ['isEnabled']);

        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->object = new \Magento\Msrp\Pricing\Price\MsrpPrice(
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
            ->willReturn(true);

        $this->assertTrue($this->object->isShowPriceOnGesture());
    }

    public function testIsShowPriceOnGestureFalse()
    {
        $this->helper->expects($this->once())
            ->method('isShowPriceOnGesture')
            ->with($this->equalTo($this->saleableItem))
            ->willReturn(false);

        $this->assertFalse($this->object->isShowPriceOnGesture());
    }

    public function testGetMsrpPriceMessage()
    {
        $expectedMessage = 'test';
        $this->helper->expects($this->once())
            ->method('getMsrpPriceMessage')
            ->with($this->equalTo($this->saleableItem))
            ->willReturn($expectedMessage);

        $this->assertEquals($expectedMessage, $this->object->getMsrpPriceMessage());
    }

    public function testIsMsrpEnabled()
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->assertTrue($this->object->isMsrpEnabled());
    }

    public function testCanApplyMsrp()
    {
        $this->helper->expects($this->once())
            ->method('canApplyMsrp')
            ->with($this->equalTo($this->saleableItem))
            ->willReturn(true);

        $this->assertTrue($this->object->canApplyMsrp($this->saleableItem));
    }
}
