<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Plugin\AddStockItemsProducts;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddStockItemsProductsTest extends TestCase
{
    /**
     * @var AddStockItemsProduct
     */
    protected $plugin;

    /**
     * @var StockRegistryInterface|MockObject
     */
    private $stockRegistryMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var ProductExtensionInterface|MockObject
     */
    protected $productExtensionMock;

    /**
     * @var Collection
     */
    protected $productCollection;

    protected function setUp(): void
    {
        $this->stockRegistryMock = $this->getMockForAbstractClass(StockRegistryInterface::class);

        $this->plugin = new AddStockItemsProducts(
            $this->stockRegistryMock
        );

    }

    public function testafterGetItems()
    {
        $productId = 1;
        $stockItemMock = $this->getMockForAbstractClass(StockItemInterface::class);

        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId)
            ->willReturn($stockItemMock);

        $this->productExtensionMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['setStockItem'])
            ->getMockForAbstractClass();
        $this->productExtensionMock->expects($this->once())
            ->method('setStockItem')
            ->with($stockItemMock)
            ->willReturnSelf();

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->productExtensionMock)
            ->willReturnSelf();
        $this->productMock->expects(($this->once()))
            ->method('getId')
            ->willReturn($productId);

        $this->productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$productId => $this->productMock]);

        $this->assertEquals(
            $this->productCollection,
            $this->plugin->afterGetItems($this->productCollection)
        );

    }
}
