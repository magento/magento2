<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Msrp\Helper\Data;
use Magento\Msrp\Model\Config;
use Magento\Msrp\Pricing\Price\MsrpPrice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MsrpPriceTest extends TestCase
{
    /**
     * @var MsrpPrice
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $helper;

    /**
     * @var MockObject
     */
    protected $saleableItem;

    /**
     * @var BasePrice|MockObject
     */
    protected $price;

    /**
     * @var Base|MockObject
     */
    protected $priceInfo;

    /**
     * @var Calculator|MockObject
     */
    protected $calculator;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->saleableItem = $this->createPartialMock(
            Product::class,
            ['getPriceInfo', '__wakeup']
        );

        $this->priceInfo = $this->createMock(Base::class);
        $this->price = $this->createMock(BasePrice::class);

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

        $this->calculator = $this->getMockBuilder(Calculator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->createPartialMock(
            Data::class,
            ['isShowPriceOnGesture', 'getMsrpPriceMessage', 'canApplyMsrp']
        );
        $this->config = $this->createPartialMock(Config::class, ['isEnabled']);

        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

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
