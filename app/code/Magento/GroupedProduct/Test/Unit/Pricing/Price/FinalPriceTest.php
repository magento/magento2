<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProduct\Pricing\Price\FinalPrice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FinalPriceTest extends TestCase
{
    /**
     * @var FinalPrice
     */
    protected $finalPrice;

    /**
     * @var Product|MockObject
     */
    protected $saleableItemMock;

    /**
     * @var Calculator|MockObject
     */
    protected $calculatorMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->saleableItemMock =  $this->createMock(Product::class);
        $this->calculatorMock = $this->createMock(Calculator::class);

        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $this->finalPrice = new FinalPrice(
            $this->saleableItemMock,
            null,
            $this->calculatorMock,
            $this->priceCurrencyMock
        );
    }

    public function testGetMinProduct()
    {
        $product1 = $this->getProductMock(10);
        $product2 = $this->getProductMock(20);

        $typeInstanceMock = $this->createMock(Grouped::class);
        $typeInstanceMock->expects($this->once())
            ->method('getAssociatedProducts')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue([$product1, $product2]));

        $this->saleableItemMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $this->assertEquals($product1, $this->finalPrice->getMinProduct());
    }

    public function testGetValue()
    {
        $product1 = $this->getProductMock(10);
        $product2 = $this->getProductMock(20);

        $typeInstanceMock = $this->createMock(Grouped::class);
        $typeInstanceMock->expects($this->once())
            ->method('getAssociatedProducts')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue([$product1, $product2]));

        $this->saleableItemMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $this->assertEquals(10, $this->finalPrice->getValue());
    }

    public function testGetValueWithoutMinProduct()
    {
        $typeInstanceMock = $this->createMock(
            Grouped::class
        );
        $typeInstanceMock->expects($this->once())
            ->method('getAssociatedProducts')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue([]));

        $this->saleableItemMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $this->assertEquals(0.00, $this->finalPrice->getValue());
    }

    /**
     * @param $price
     * @return MockObject
     */
    protected function getProductMock($price)
    {
        $priceTypeMock = $this->createMock(\Magento\Catalog\Pricing\Price\FinalPrice::class);
        $priceTypeMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($price));

        $priceInfoMock = $this->createMock(Base::class);
        $priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE))
            ->will($this->returnValue($priceTypeMock));

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())
            ->method('setQty')
            ->with($this->equalTo(PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT));
        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        return $productMock;
    }
}
