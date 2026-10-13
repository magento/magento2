<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProduct\Pricing\Price\ConfiguredPrice;
use Magento\GroupedProduct\Pricing\Price\FinalPrice;
use Magento\Store\Model\Store;
use Magento\Wishlist\Model\Item\Option;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfiguredPriceTest extends TestCase
{
    /**
     * @var ConfiguredPrice
     */
    protected $model;

    /**
     * @var SaleableInterface|MockObject
     */
    protected $saleableItem;

    /**
     * @var CalculatorInterface|MockObject
     */
    protected $calculator;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    /**
     * @var PriceInterface|MockObject
     */
    protected $price;

    /**
     * @var PriceInfoInterface|MockObject
     */
    protected $priceInfo;

    protected function setUp(): void
    {
        $this->price = $this->createMock(PriceInterface::class);

        $this->priceInfo = $this->createMock(PriceInfoInterface::class);

        $this->saleableItem = $this->createMock(Product::class);
        $this->saleableItem->method('getPriceInfo')->willReturn($this->priceInfo);

        $this->calculator = $this->createMock(CalculatorInterface::class);

        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);

        $this->model = new ConfiguredPrice(
            $this->saleableItem,
            null,
            $this->calculator,
            $this->priceCurrency
        );
    }

    public function testSetItem()
    {
        $item = $this->createMock(ItemInterface::class);

        $this->model->setItem($item);
    }

    public function testGetValueNoItem()
    {
        $resultPrice = rand(1, 9);

        $this->price->expects($this->once())
            ->method('getValue')
            ->willReturn($resultPrice);

        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with(BasePrice::PRICE_CODE)
            ->willReturn($this->price);

        $this->assertEquals($resultPrice, $this->model->getValue());
    }

    public function testGetValue()
    {
        $resultPrice = rand(1, 9);
        $customOptionOneQty = rand(1, 9);
        $customOptionTwoQty = rand(1, 9);

        $priceInfoBase = $this->createMock(Base::class);
        $priceInfoBase->expects($this->any())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($this->price);

        $productOne = $this->createMock(Product::class);
        $productOne->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $productOne->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoBase);

        $productTwo = $this->createMock(Product::class);
        $productTwo->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $productTwo->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoBase);

        $this->price->method('getValue')->willReturn($resultPrice);

        $customOptionOne = $this->createMock(Option::class);
        $customOptionOne->method('getValue')->willReturn($customOptionOneQty);

        $customOptionTwo = $this->createMock(Option::class);
        $customOptionTwo->method('getValue')->willReturn($customOptionTwoQty);

        $store = $this->createMock(Store::class);

        $groupedProduct = $this->createMock(Grouped::class);
        $groupedProduct->expects($this->once())
            ->method('setStoreFilter')
            ->with($store, $this->saleableItem)
            ->willReturnSelf();
        $groupedProduct->expects($this->once())
            ->method('getAssociatedProducts')
            ->with($this->saleableItem)
            ->willReturn([$productOne, $productTwo]);

        $this->saleableItem->method('getTypeInstance')->willReturn($groupedProduct);
        $this->saleableItem->method('getStore')->willReturn($store);
        $this->saleableItem->method('getCustomOption')->willReturnMap([
            ['associated_product_1', $customOptionOne],
            ['associated_product_2', $customOptionTwo]
        ]);

        $item = $this->createMock(ItemInterface::class);

        $this->model->setItem($item);

        $result = 0;
        foreach ([$customOptionOneQty, $customOptionTwoQty] as $qty) {
            $result += $resultPrice * $qty;
        }

        $this->assertEquals($result, $this->model->getValue());
    }

    public function testGetAmount()
    {
        $resultPrice = rand(1, 9);

        $this->price->method('getValue')
            ->willReturn($resultPrice);

        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with(BasePrice::PRICE_CODE)
            ->willReturn($this->price);

        $this->calculator->expects($this->once())
            ->method('getAmount')
            ->with($resultPrice, $this->saleableItem)
            ->willReturn($resultPrice);

        $this->assertEquals($resultPrice, $this->model->getAmount());
    }
}
