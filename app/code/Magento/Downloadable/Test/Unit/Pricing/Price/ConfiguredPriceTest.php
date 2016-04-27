<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Pricing\Price;

class ConfiguredPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saleableItem;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\CalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $calculator;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Downloadable\Pricing\Price\ConfiguredPrice
     */
    private $model;

    /**
     * @var \Magento\Framework\Pricing\PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceInfoMock;

    protected function setUp()
    {
        $this->priceInfoMock = $this->getMockBuilder('Magento\Framework\Pricing\PriceInfoInterface')
            ->getMockForAbstractClass();

        $this->saleableItem = $this->getMockBuilder('Magento\Framework\Pricing\SaleableInterface')
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

        $this->calculator = $this->getMockBuilder('Magento\Framework\Pricing\Adjustment\CalculatorInterface')
            ->getMockForAbstractClass();

        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->getMockForAbstractClass();

        $this->model = new \Magento\Downloadable\Pricing\Price\ConfiguredPrice(
            $this->saleableItem,
            null,
            $this->calculator,
            $this->priceCurrency
        );
    }

    public function testGetValue()
    {
        $priceValue = 10;

        $wishlistItemOptionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1,2');

        $linkMock = $this->getMockBuilder('Magento\Downloadable\Model\Link')
            ->disableOriginalConstructor()
            ->getMock();
        $linkMock->expects($this->once())
            ->method('getPrice')
            ->willReturn(10);

        $productTypeMock = $this->getMockBuilder('Magento\Downloadable\Model\Product\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock->expects($this->once())
            ->method('getLinks')
            ->with($this->saleableItem)
            ->willReturn([1 => $linkMock]);

        $priceMock = $this->getMockBuilder('Magento\Framework\Pricing\Price\PriceInterface')
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(\Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE)
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

        $result = $this->model->getValue();
        $this->assertEquals(20, $result);
    }

    public function testGetValueNoLinksPurchasedSeparately()
    {
        $priceValue = 10;

        $priceMock = $this->getMockBuilder('Magento\Framework\Pricing\Price\PriceInterface')
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(\Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE)
            ->willReturn($priceMock);

        $this->saleableItem->expects($this->once())
            ->method('getLinksPurchasedSeparately')
            ->willReturn(false);

        $result = $this->model->getValue();
        $this->assertEquals($priceValue, $result);
    }

    public function testGetValueNoOptions()
    {
        $priceValue = 10;

        $priceMock = $this->getMockBuilder('Magento\Framework\Pricing\Price\PriceInterface')
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(\Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE)
            ->willReturn($priceMock);

        $wishlistItemOptionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn(null);

        $productTypeMock = $this->getMockBuilder('Magento\Downloadable\Model\Product\Type')
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

        $result = $this->model->getValue();
        $this->assertEquals($priceValue, $result);
    }
}
