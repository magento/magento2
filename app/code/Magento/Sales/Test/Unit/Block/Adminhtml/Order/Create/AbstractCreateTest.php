<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Downloadable\Pricing\Price\LinkPrice;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate;
use Magento\Wishlist\Model\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractCreateTest extends TestCase
{
    /**
     * @var AbstractCreate|MockObject
     */
    protected $model;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Base|MockObject
     */
    protected $priceInfoMock;

    /**
     * @var LinkPrice|MockObject
     */
    protected $linkPriceMock;

    protected function setUp(): void
    {
        $this->model = $this->getMockBuilder(AbstractCreate::class)
            ->onlyMethods(['convertPrice'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->priceInfoMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkPriceMock = $this->getMockBuilder(LinkPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
    }

    public function testGetItemPrice()
    {
        $price = 5.6;
        $resultPrice = 9.3;

        $this->linkPriceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($price);
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($this->linkPriceMock);
        $this->model->expects($this->once())
            ->method('convertPrice')
            ->with($price)
            ->willReturn($resultPrice);
        $this->assertEquals($resultPrice, $this->model->getItemPrice($this->productMock));
    }

    /**
     * @param $item
     *
     * @dataProvider getProductDataProvider
     */
    public function testGetProduct($item)
    {
        $item = $item($this);
        $product = $this->model->getProduct($item);

        self::assertInstanceOf(Product::class, $product);
    }

    protected function getMockForItemClass() {
        $productMock = $this->createMock(Product::class);
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())->method('getProduct')->willReturn($productMock);

        return $itemMock;
    }

    /**
     * DataProvider for testGetProduct.
     *
     * @return array
     */
    public static function getProductDataProvider()
    {
        $productMock = static fn (self $testCase) => $testCase->createMock(Product::class);
        $itemMock = static fn (self $testCase) => $testCase->getMockForItemClass();

        return [
            [$productMock],
            [$itemMock],
        ];
    }
}
