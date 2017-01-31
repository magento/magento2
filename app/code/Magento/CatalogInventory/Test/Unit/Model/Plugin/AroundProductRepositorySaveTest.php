<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Plugin\AroundProductRepositorySave;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Unit test for Magento\CatalogInventory\Model\Plugin\AroundProductRepositorySave
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AroundProductRepositorySaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var ProductInterface
     */
    private $savedProduct;

    /**
     * @var ProductExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productExtension;

    /**
     * @var StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockItem;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var StockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultStock;

    /**
     * @var StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockRegistry;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Model\Plugin\AroundProductRepositorySave
     */
    private $plugin;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->stockRegistry = $this->getMockBuilder(StockRegistryInterface::class)
            ->setMethods(['getStockItem', 'updateStockItemBySku'])
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->stockConfiguration = $this->getMockBuilder(StockConfigurationInterface::class)
            ->setMethods(['getDefaultScopeId'])
            ->getMockForAbstractClass();

        $this->plugin = new AroundProductRepositorySave(
            $this->stockRegistry,
            $this->storeManager,
            $this->stockConfiguration
        );

        $this->savedProduct = $savedProduct = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getExtensionAttributes', 'getStoreId'])
            ->getMockForAbstractClass();

        $this->closure = function () use ($savedProduct) {
            return $savedProduct;
        };

        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->product = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getExtensionAttributes', 'getStoreId'])
            ->getMockForAbstractClass();
        $this->productExtension = $this->getMockBuilder(ProductExtension::class)
            ->setMethods(['getStockItem'])
            ->getMock();
        $this->stockItem = $this->getMockBuilder(StockItemInterface::class)
            ->setMethods(['setWebsiteId', 'getWebsiteId', 'getStockId'])
            ->getMockForAbstractClass();
        $this->defaultStock = $this->getMockBuilder(StockInterface::class)
            ->setMethods(['getStockId'])
            ->getMockForAbstractClass();
    }

    public function testAroundSaveWhenProductHasNoStockItemNeedingToBeUpdated()
    {
        // pretend we have no extension attributes at all
        $this->product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $this->productExtension->expects($this->never())->method('getStockItem');

        // pretend that the product already has existing stock item information
        $this->stockRegistry->expects($this->once())->method('getStockItem')->willReturn($this->stockItem);
        $this->stockItem->expects($this->once())->method('getItemId')->willReturn(1);
        $this->stockItem->expects($this->never())->method('setProductId');
        $this->stockItem->expects($this->never())->method('setWebsiteId');

        // expect that there are no changes to the existing stock item information
        $result = $this->plugin->aroundSave($this->productRepository, $this->closure, $this->product);
        $this->assertEquals(
            $this->savedProduct,
            $result
        );
    }

    public function testAroundSaveWhenProductHasNoPersistentStockItemInfo()
    {
        // pretend we do have extension attributes, but none for 'stock_item'
        $this->product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtension);
        $this->productExtension->expects($this->once())
            ->method('getStockItem')
            ->willReturn(null);

        $this->stockConfiguration->expects($this->once())->method('getDefaultScopeId')->willReturn(1);
        $this->stockRegistry->expects($this->once())->method('getStockItem')->willReturn($this->stockItem);
        $this->stockRegistry->expects($this->once())->method('updateStockItemBySku');

        $this->stockItem->expects($this->once())->method('getItemId')->willReturn(null);
        $this->stockItem->expects($this->once())->method('setProductId');
        $this->stockItem->expects($this->once())->method('setWebsiteId');
        $this->product->expects(($this->atLeastOnce()))->method('getStoreId')->willReturn(20);

        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepository->expects($this->once())->method('get')->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
            $this->plugin->aroundSave($this->productRepository, $this->closure, $this->product)
        );
    }

    public function testAroundSave()
    {
        $productId = 5494;
        $storeId = 2;
        $sku = 'my product that needs saving';
        $defaultScopeId = 100;
        $this->stockConfiguration->expects($this->exactly(2))
            ->method('getDefaultScopeId')
            ->willReturn($defaultScopeId);
        $this->stockRegistry->expects($this->once())
            ->method('getStock')
            ->with($defaultScopeId)
            ->willReturn($this->defaultStock);

        $this->product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtension);
        $this->productExtension->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItem);

        $storedStockItem = $this->getMockBuilder(StockItemInterface::class)
            ->setMethods(['getItemId'])
            ->getMockForAbstractClass();
        $storedStockItem->expects($this->once())
            ->method('getItemId')
            ->willReturn(500);
        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->willReturn($storedStockItem);

        $this->product->expects(($this->exactly(2)))->method('getId')->willReturn($productId);
        $this->product->expects(($this->atLeastOnce()))->method('getStoreId')->willReturn($storeId);
        $this->product->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);

        $this->stockItem->expects($this->once())->method('setProductId')->with($productId);
        $this->stockItem->expects($this->once())->method('setWebsiteId')->with($defaultScopeId);

        $this->stockRegistry->expects($this->once())
            ->method('updateStockItemBySku')
            ->with($sku, $this->stockItem);

        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepository->expects($this->once())
            ->method('get')
            ->with($sku, false, $storeId, true)
            ->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
            $this->plugin->aroundSave($this->productRepository, $this->closure, $this->product)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid stock id: 100500. Only default stock with id 50 allowed
     */
    public function testAroundSaveWithInvalidStockId()
    {
        $stockId = 100500;
        $defaultScopeId = 100;
        $defaultStockId = 50;

        $this->stockItem->expects($this->once())
            ->method('getStockId')
            ->willReturn($stockId);
        $this->stockRegistry->expects($this->once())
            ->method('getStock')
            ->with($defaultScopeId)
            ->willReturn($this->defaultStock);
        $this->stockConfiguration->expects($this->once())
            ->method('getDefaultScopeId')
            ->willReturn($defaultScopeId);
        $this->defaultStock->expects($this->once())
            ->method('getStockId')
            ->willReturn($defaultStockId);

        $this->product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtension);
        $this->productExtension->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItem);

        $this->plugin->aroundSave($this->productRepository, $this->closure, $this->product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid stock item id: 0. Should be null or numeric value greater than 0
     */
    public function testAroundSaveWithInvalidStockItemId()
    {
        $stockId = 80;
        $stockItemId = 0;
        $defaultScopeId = 100;
        $defaultStockId = 80;

        $this->stockItem->expects($this->once())
            ->method('getStockId')
            ->willReturn($stockId);
        $this->stockRegistry->expects($this->once())
            ->method('getStock')
            ->with($defaultScopeId)
            ->willReturn($this->defaultStock);
        $this->stockConfiguration->expects($this->once())
            ->method('getDefaultScopeId')
            ->willReturn($defaultScopeId);
        $this->defaultStock->expects($this->once())
            ->method('getStockId')
            ->willReturn($defaultStockId);

        $this->product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtension);
        $this->productExtension->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItem);

        $this->stockItem->expects($this->once())
            ->method('getItemId')
            ->willReturn($stockItemId);

        $this->plugin->aroundSave($this->productRepository, $this->closure, $this->product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid stock item id: 35. Assigned stock item id is 40
     */
    public function testAroundSaveWithNotAssignedStockItemId()
    {
        $stockId = 80;
        $stockItemId = 35;
        $defaultScopeId = 100;
        $defaultStockId = 80;
        $storedStockitemId = 40;

        $this->stockItem->expects($this->once())
            ->method('getStockId')
            ->willReturn($stockId);
        $this->stockRegistry->expects($this->once())
            ->method('getStock')
            ->with($defaultScopeId)
            ->willReturn($this->defaultStock);
        $this->stockConfiguration->expects($this->once())
            ->method('getDefaultScopeId')
            ->willReturn($defaultScopeId);
        $this->defaultStock->expects($this->once())
            ->method('getStockId')
            ->willReturn($defaultStockId);

        $this->product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtension);
        $this->productExtension->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItem);

        $this->stockItem->expects($this->once())
            ->method('getItemId')
            ->willReturn($stockItemId);

        $storedStockItem = $this->getMockBuilder(StockItemInterface::class)
            ->setMethods(['getItemId'])
            ->getMockForAbstractClass();
        $storedStockItem->expects($this->once())
            ->method('getItemId')
            ->willReturn($storedStockitemId);
        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->willReturn($storedStockItem);

        $this->plugin->aroundSave($this->productRepository, $this->closure, $this->product);
    }
}
