<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Pricing\ConfiguredPrice;

use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Wishlist\Pricing\ConfiguredPrice\Downloadable;

class DownloadableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saleableItem;

    /**
     * @var CalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $calculator;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var Downloadable
     */
    private $model;

    /**
     * @var PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceInfoMock;

    protected function setUp()
    {
        $this->priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfoInterface::class)
            ->getMockForAbstractClass();

        $this->saleableItem = $this->getMockBuilder(\Magento\Framework\Pricing\SaleableInterface::class)
            ->setMethods([
                'getPriceInfo',
                'getLinksPurchasedSeparately',
                'getCustomOption',
                'getTypeInstance',
            ])
            ->getMockForAbstractClass();
        $this->saleableItem->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->calculator = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\CalculatorInterface::class)
            ->getMockForAbstractClass();

        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Downloadable(
            $this->saleableItem,
            null,
            $this->calculator,
            $this->priceCurrency
        );
    }

    public function testGetValue()
    {
        $priceValue = 10;

        $wishlistItemOptionMock = $this->getMockBuilder(\Magento\Wishlist\Model\Item\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1,2');

        $linkMock = $this->getMockBuilder(\Magento\Downloadable\Model\Link::class)
            ->disableOriginalConstructor()
            ->getMock();
        $linkMock->expects($this->once())
            ->method('getPrice')
            ->willReturn(10);

        $productTypeMock = $this->getMockBuilder(\Magento\Downloadable\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock->expects($this->once())
            ->method('getLinks')
            ->with($this->saleableItem)
            ->willReturn([1 => $linkMock]);

        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(BasePrice::PRICE_CODE)
            ->willReturn($priceMock);

        $this->saleableItem->expects($this->once())
            ->method('getLinksPurchasedSeparately')
            ->willReturn(true);
        $this->saleableItem->expects($this->once())
            ->method('getCustomOption')
            ->with('downloadable_link_ids')
            ->willReturn($wishlistItemOptionMock);
        $this->saleableItem->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);

        $this->assertEquals(20, $this->model->getValue());
    }

    public function testGetValueNoLinksPurchasedSeparately()
    {
        $priceValue = 10;

        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(BasePrice::PRICE_CODE)
            ->willReturn($priceMock);

        $this->saleableItem->expects($this->once())
            ->method('getLinksPurchasedSeparately')
            ->willReturn(false);

        $this->assertEquals($priceValue, $this->model->getValue());
    }

    public function testGetValueNoOptions()
    {
        $priceValue = 10;

        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(BasePrice::PRICE_CODE)
            ->willReturn($priceMock);

        $wishlistItemOptionMock = $this->getMockBuilder(\Magento\Wishlist\Model\Item\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn(null);

        $productTypeMock = $this->getMockBuilder(\Magento\Downloadable\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock->expects($this->once())
            ->method('getLinks')
            ->with($this->saleableItem)
            ->willReturn([]);

        $this->saleableItem->expects($this->once())
            ->method('getLinksPurchasedSeparately')
            ->willReturn(true);
        $this->saleableItem->expects($this->once())
            ->method('getCustomOption')
            ->with('downloadable_link_ids')
            ->willReturn($wishlistItemOptionMock);
        $this->saleableItem->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);

        $this->assertEquals($priceValue, $this->model->getValue());
    }

    public function testGetValueWithNoCustomOption()
    {
        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn(0);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(BasePrice::PRICE_CODE)
            ->willReturn($priceMock);

        $this->saleableItem->expects($this->once())
            ->method('getLinksPurchasedSeparately')
            ->willReturn(true);
        $this->saleableItem->expects($this->once())
            ->method('getCustomOption')
            ->with('downloadable_link_ids')
            ->willReturn(null);

        $this->assertEquals(0, $this->model->getValue());
    }
}
