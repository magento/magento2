<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Pricing\ConfiguredPrice;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Wishlist\Pricing\ConfiguredPrice\ConfigurableProduct;

class ConfigurableProductTest extends \PHPUnit_Framework_TestCase
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
     * @var ConfigurableProduct
     */
    private $model;

    /**
     * @var PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceInfoMock;

    protected function setUp()
    {
        $this->priceInfoMock = $this->getMockBuilder('Magento\Framework\Pricing\PriceInfoInterface')
            ->getMockForAbstractClass();
        
        $this->saleableItem = $this->getMockBuilder('Magento\Framework\Pricing\SaleableInterface')
            ->setMethods([
                'getPriceInfo',
                'getCustomOption',
            ])
            ->getMockForAbstractClass();
        $this->saleableItem->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->calculator = $this->getMockBuilder('Magento\Framework\Pricing\Adjustment\CalculatorInterface')
            ->getMockForAbstractClass();

        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->getMockForAbstractClass();

        $this->model = new ConfigurableProduct(
            $this->saleableItem,
            null,
            $this->calculator,
            $this->priceCurrency
        );
    }

    public function testGetValue()
    {
        $priceValue = 10;

        $priceMock = $this->getMockBuilder('Magento\Framework\Pricing\Price\PriceInterface')
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock = $this->getMockBuilder('Magento\Framework\Pricing\PriceInfo\Base')
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableProduct::PRICE_CODE)
            ->willReturn($priceMock);

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $wishlistItemOptionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
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
        $this->saleableItem->expects($this->once())
            ->method('getCustomOption')
            ->with('simple_product')
            ->willReturn(null);

        $this->assertEquals(0, $this->model->getValue());
    }
}
