<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\CartItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\StockState;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\QuoteGraphQl\Model\CartItem\ProductStock;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ProductStock::isProductAvailable()
 */
class ProductStockTest extends TestCase
{
    /**
     * @var ProductStock
     */
    private $productStock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var StockState|MockObject
     */
    private $stockStateMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StockRegistryInterface|MockObject
     */
    private $stockRegistryMock;

    /**
     * @var Item|MockObject
     */
    private $cartItemMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $productMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var StockStatusInterface|MockObject
     */
    private $stockStatusMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $optionProductMock;

    /**
     * @var Option|MockObject
     */
    private $qtyOptionMock;

    /**
     * Set up mocks and initialize the ProductStock class
     */
    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->stockStateMock = $this->createMock(StockState::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->stockRegistryMock = $this->createMock(StockRegistryInterface::class);
        $this->productStock = new ProductStock(
            $this->productRepositoryMock,
            $this->stockStateMock,
            $this->scopeConfigMock,
            $this->stockRegistryMock
        );
        $this->stockStatusMock = $this->createMock(StockStatusInterface::class);
        $this->cartItemMock = $this->createPartialMock(
            \Magento\Quote\Test\Unit\Helper\QuoteItemUpdaterTestHelper::class,
            [
                'getStore',
                'getProductType',
                'getProduct',
                'getChildren',
                'getQtyOptions',
                'getQtyToAdd',
                'getPreviousQty'
            ]
        );
        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Test\Unit\Helper\ProductTestHelper::class,
            ['getId', 'getStore']
        );
        $this->optionProductMock = $this->createPartialMock(
            \Magento\Catalog\Test\Unit\Helper\ProductTestHelper::class,
            ['getId', 'getStore']
        );
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->qtyOptionMock = $this->createMock(Option::class);
    }

    /**
     * Test isProductAvailable() for a simple product with sufficient stock
     */
    public function testIsProductAvailableForSimpleProductWithStock(): void
    {
        $this->cartItemMock->expects($this->exactly(2))
            ->method('getProductType')
            ->willReturn('simple');
        $this->cartItemMock->expects($this->once())
            ->method('getQtyToAdd')
            ->willReturn(2.0);
        $this->cartItemMock->expects($this->once())
            ->method('getPreviousQty')
            ->willReturn(1.0);
        $this->cartItemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->cartItemMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn(123);
        $this->productMock->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(true);
        $this->stockStatusMock->expects($this->once())
            ->method('getQty')
            ->willReturn(10);
        $this->stockStateMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with(123, 2.0, 3.0, 1.0, 1)
            ->willReturn($this->stockStatusMock);
        $this->stockRegistryMock->expects($this->exactly(2))
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $this->cartItemMock->expects($this->never())->method('getChildren');
        $result = $this->productStock->isProductAvailable($this->cartItemMock);
        $this->assertTrue($result);
    }

    /**
     * Test isProductAvailable() for a simple product with insufficient stock
     */
    public function testIsProductAvailableForSimpleProductWithoutStock()
    {
        $this->cartItemMock->expects($this->exactly(2))
            ->method('getProductType')
            ->willReturn('simple');
        $this->cartItemMock->expects($this->once())
            ->method('getQtyToAdd')
            ->willReturn(2.0);
        $this->cartItemMock->expects($this->once())
            ->method('getPreviousQty')
            ->willReturn(1.0);
        $this->cartItemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->cartItemMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(123);
        $this->productMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->stockStateMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with(123, 2.0, 3.0, 1.0, 1)
            ->willReturn($this->stockStatusMock);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(false);
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $this->cartItemMock->expects($this->never())->method('getChildren');
        $result = $this->productStock->isProductAvailable($this->cartItemMock);
        $this->assertFalse($result);
    }

    /**
     * Test isStockAvailableBundle when stock is available
     */
    public function testIsStockAvailableBundleStockAvailable()
    {
        $this->qtyOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn(1.0);
        $this->qtyOptionMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->optionProductMock);
        $this->cartItemMock->expects($this->once())
            ->method('getQtyOptions')
            ->willReturn([$this->qtyOptionMock]);
        $this->cartItemMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->optionProductMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn(789);
        $this->optionProductMock->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->stockStateMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with(789, 2.0, 3.0, 1.0, 1)
            ->willReturn($this->stockStatusMock);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(true);
        $this->stockRegistryMock->expects($this->exactly(2))
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $this->stockStatusMock->expects($this->once())
            ->method('getQty')
            ->willReturn(10);
        $result = $this->productStock->isStockAvailableBundle($this->cartItemMock, 1, 2.0);
        $this->assertTrue($result);
    }

    /**
     * Test isStockAvailableBundle when stock is not available
     */
    public function testIsStockAvailableBundleStockNotAvailable()
    {
        $this->qtyOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn(2.0);
        $this->qtyOptionMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->optionProductMock);
        $this->cartItemMock->expects($this->once())
            ->method('getQtyOptions')
            ->willReturn([$this->qtyOptionMock]);
        $this->cartItemMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->optionProductMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(789);
        $this->optionProductMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->stockStateMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with(789, 2.0, 6.0, 1.0, 1)
            ->willReturn($this->stockStatusMock);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(false);
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $result = $this->productStock->isStockAvailableBundle($this->cartItemMock, 1, 2.0);
        $this->assertFalse($result);
    }
}
