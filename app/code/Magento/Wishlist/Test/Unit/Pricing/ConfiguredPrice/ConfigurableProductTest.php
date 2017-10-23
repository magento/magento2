<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Pricing\ConfiguredPrice;

class ConfigurableProductTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Wishlist\Pricing\ConfiguredPrice\ConfigurableProduct
     */
    private $model;

    /**
     * @var \Magento\Framework\Pricing\PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceInfoMock;

    protected function setUp()
    {
        $this->priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfoInterface::class)
            ->getMockForAbstractClass();
        
        $this->saleableItem = $this->getMockBuilder(\Magento\Framework\Pricing\SaleableInterface::class)
            ->setMethods([
                'getPriceInfo',
                'getCustomOption',
            ])
            ->getMockForAbstractClass();

        $this->calculator = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\CalculatorInterface::class)
            ->getMockForAbstractClass();

        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMockForAbstractClass();

        $this->model = new \Magento\Wishlist\Pricing\ConfiguredPrice\ConfigurableProduct(
            $this->saleableItem,
            null,
            $this->calculator,
            $this->priceCurrency
        );
    }

    public function testGetValue()
    {
        $priceValue = 10;

        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(\Magento\Wishlist\Pricing\ConfiguredPrice\ConfigurableProduct::PRICE_CODE)
            ->willReturn($priceMock);

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $wishlistItemOptionMock = $this->getMockBuilder(\Magento\Wishlist\Model\Item\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemOptionMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $this->saleableItem->expects($this->once())
            ->method('getCustomOption')
            ->with('simple_product')
            ->willReturn($wishlistItemOptionMock);

        $this->assertEquals($priceValue, $this->model->getValue());
    }

    public function testGetValueWithNoCustomOption()
    {
        $priceValue = 100;

        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->saleableItem->expects($this->once())
            ->method('getCustomOption')
            ->with('simple_product')
            ->willReturn(null);

        $this->saleableItem->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(\Magento\Wishlist\Pricing\ConfiguredPrice\ConfigurableProduct::PRICE_CODE)
            ->willReturn($priceMock);

        $this->assertEquals(100, $this->model->getValue());
    }
}
