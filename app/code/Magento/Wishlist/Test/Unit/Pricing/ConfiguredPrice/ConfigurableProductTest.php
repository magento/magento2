<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Pricing\ConfiguredPrice;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Pricing\ConfiguredPrice\ConfigurableProduct;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurableProductTest extends TestCase
{
    /**
     * @var SaleableInterface|MockObject
     */
    private $saleableItem;

    /**
     * @var CalculatorInterface|MockObject
     */
    private $calculator;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrency;

    /**
     * @var ConfigurableProduct
     */
    private $model;

    /**
     * @var PriceInfoInterface|MockObject
     */
    private $priceInfoMock;

    protected function setUp(): void
    {
        $this->priceInfoMock = $this->getMockBuilder(PriceInfoInterface::class)
            ->getMockForAbstractClass();

        $this->saleableItem = $this->getMockBuilder(SaleableInterface::class)
            ->setMethods([
                'getPriceInfo',
                'getCustomOption',
            ])
            ->getMockForAbstractClass();

        $this->calculator = $this->getMockBuilder(CalculatorInterface::class)
            ->getMockForAbstractClass();

        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
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

        $priceMock = $this->getMockBuilder(PriceInterface::class)
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableProduct::PRICE_CODE)
            ->willReturn($priceMock);

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $wishlistItemOptionMock = $this->getMockBuilder(Option::class)
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

        $priceMock = $this->getMockBuilder(PriceInterface::class)
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
            ->with(ConfigurableProduct::PRICE_CODE)
            ->willReturn($priceMock);

        $this->assertEquals(100, $this->model->getValue());
    }
}
