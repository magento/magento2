<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Model\Plugin\ProductListStockItem;
use Magento\CatalogInventory\Model\StockRegistryPreloader;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductListStockItemTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var StockRegistryPreloader|MockObject
     */
    private $preloader;

    /**
     * @var StockItemInterfaceFactory|MockObject
     */
    private $stockItemFactory;

    /**
     * @var ProductListStockItem
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->preloader = $this->createMock(StockRegistryPreloader::class);
        $this->stockItemFactory = $this->createMock(StockItemInterfaceFactory::class);
        $this->plugin = new ProductListStockItem($this->preloader, $this->stockItemFactory);
    }

    public function testAfterGetListSetsStockItemOnEveryProductWithOneLoad(): void
    {
        $foundStockItem = $this->createStub(StockItemInterface::class);
        $foundStockItem->method('getProductId')->willReturn(5);
        $emptyStockItem = $this->createStub(StockItemInterface::class);

        $this->preloader->expects($this->once())
            ->method('preloadStockItems')
            ->with([5, 6])
            ->willReturn([$foundStockItem]);
        $this->stockItemFactory->expects($this->once())
            ->method('create')
            ->willReturn($emptyStockItem);

        $extensionWithStock = $this->createPartialMockWithReflection(ProductExtension::class, ['setStockItem']);
        $extensionWithStock->expects($this->once())->method('setStockItem')->with($foundStockItem);
        $extensionWithoutStock = $this->createPartialMockWithReflection(ProductExtension::class, ['setStockItem']);
        $extensionWithoutStock->expects($this->once())->method('setStockItem')->with($emptyStockItem);

        $result = $this->createStub(ProductSearchResultsInterface::class);
        $result->method('getItems')->willReturn([
            $this->createProduct('5', $extensionWithStock),
            $this->createProduct('6', $extensionWithoutStock),
        ]);

        $this->assertSame($result, $this->invokePlugin($result));
    }

    public function testAfterGetListSkipsLoadForEmptyResult(): void
    {
        $this->preloader->expects($this->never())->method('preloadStockItems');
        $result = $this->createStub(ProductSearchResultsInterface::class);
        $result->method('getItems')->willReturn([]);

        $this->assertSame($result, $this->invokePlugin($result));
    }

    private function invokePlugin(ProductSearchResultsInterface $result): ProductSearchResultsInterface
    {
        return $this->plugin->afterGetList(
            $this->createStub(ProductRepositoryInterface::class),
            $result,
            $this->createStub(SearchCriteriaInterface::class)
        );
    }

    private function createProduct(string $id, ProductExtension $extension): Product
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn($id);
        $product->method('getExtensionAttributes')->willReturn($extension);
        $product->expects($this->once())->method('setExtensionAttributes')->with($extension);
        return $product;
    }
}
