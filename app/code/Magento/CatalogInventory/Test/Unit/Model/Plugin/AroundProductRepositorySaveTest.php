<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogInventory\Test\Unit\Model\Plugin;

class AroundProductRepositorySaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Plugin\AroundProductRepositorySave
     */
    protected $plugin;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemMock;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $savedProductMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\Data\ProductExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productExtensionMock;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    public function setUp()
    {
        $this->stockRegistry = $this->getMock('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $this->storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');

        $this->plugin = new \Magento\CatalogInventory\Model\Plugin\AroundProductRepositorySave(
            $this->stockRegistry,
            $this->storeManager
        );

        $this->productExtensionMock = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtension')
            ->setMethods(['getStockItem'])
            ->getMock();
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $this->savedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->closureMock = function () {
            return $this->savedProductMock;
        };
        $this->stockItemMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemInterface')
            ->setMethods(['setWebsiteId', 'getWebsiteId'])
            ->getMockForAbstractClass();
    }

    public function testAroundSaveWhenProductHasNoStockItemNeedingToBeUpdated()
    {
        // pretend we have no extension attributes at all
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $this->productExtensionMock->expects($this->never())->method('getStockItem');

        // pretend that the product already has existing stock item information
        $this->stockRegistry->expects($this->once())->method('getStockItem')->willReturn($this->stockItemMock);
        $this->stockItemMock->expects($this->once())->method('getItemId')->willReturn(1);
        $this->stockItemMock->expects($this->never())->method('setProductId');
        $this->stockItemMock->expects($this->never())->method('setWebsiteId');

        // expect that there are no changes to the existing stock item information
        $this->assertEquals(
            $this->savedProductMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    public function testAroundSaveWhenProductHasNoPersistentStockItemInfo()
    {
        // pretend we do have extension attributes, but none for 'stock_item'
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getStockItem')
            ->willReturn(null);

        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->stockRegistry->expects($this->once())->method('getStockItem')->willReturn($this->stockItemMock);
        $this->stockRegistry->expects($this->once())->method('updateStockItemBySku');

        $this->stockItemMock->expects($this->once())->method('getItemId')->willReturn(null);
        $this->stockItemMock->expects($this->once())->method('setProductId');
        $this->stockItemMock->expects($this->once())->method('setWebsiteId');

        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepositoryMock->expects($this->once())->method('get')->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    public function testAroundSave()
    {
        $productId = 5494;
        $websiteId = 1;
        $storeId = 2;
        $sku = 'my product that needs saving';

        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->expects($this->once())->method('getStore')->with($storeId)->willReturn($storeMock);

        $this->savedProductMock->expects(($this->once()))->method('getId')->willReturn($productId);
        $this->savedProductMock->expects(($this->atLeastOnce()))->method('getStoreId')->willReturn($storeId);
        $this->savedProductMock->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);

        $this->stockItemMock->expects($this->once())->method('setProductId')->with($productId);
        $this->stockItemMock->expects($this->once())->method('setWebsiteId')->with($websiteId);

        $this->stockRegistry->expects($this->once())
            ->method('updateStockItemBySku')
            ->with($sku, $this->stockItemMock);

        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($sku, false, $storeId, true)
            ->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }
}
