<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Product\CopyConstructor\CatalogInventory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogInventoryTest extends TestCase
{
    /**
     * @var CatalogInventory
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $duplicateMock;

    /**
     * @var MockObject
     */
    protected $stockItemDoMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var StockRegistryInterface|MockObject
     */
    protected $stockRegistry;

    protected function setUp(): void
    {
        $this->productMock = $this->createPartialMock(Product::class, ['__wakeup', 'getStore']);
        $store = $this->createPartialMock(Store::class, ['getWebsiteId', '__wakeup']);
        $store->expects($this->any())->method('getWebsiteId')->willReturn(0);
        $this->productMock->expects($this->any())->method('getStore')->willReturn($store);

        $this->duplicateMock = $this->createPartialMock(
            Product::class,
            ['setStockData']
        );

        $this->stockItemDoMock = $this->getMockForAbstractClass(
            StockItemInterface::class
        );

        $this->stockRegistry = $this->getMockForAbstractClass(
            StockRegistryInterface::class
        );

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            CatalogInventory::class,
            ['stockRegistry' => $this->stockRegistry]
        );
    }

    public function testBuildWithoutCurrentProductStockItem()
    {
        $expectedData = [
            'use_config_min_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'use_config_max_sale_qty' => 1,
            'use_config_backorders' => 1,
            'use_config_notify_stock_qty' => 1,
        ];
        $this->stockItemDoMock->expects($this->any())->method('getStockId')->willReturn(false);

        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItemDoMock);

        $this->duplicateMock->expects($this->once())->method('setStockData')->with($expectedData);
        $this->model->build($this->productMock, $this->duplicateMock);
    }

    public function testBuildWithCurrentProductStockItem()
    {
        $expectedData = [
            'use_config_min_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'use_config_max_sale_qty' => 1,
            'use_config_backorders' => 1,
            'use_config_notify_stock_qty' => 1,
            'use_config_enable_qty_inc' => 'use_config_enable_qty_inc',
            'enable_qty_increments' => 'enable_qty_increments',
            'use_config_qty_increments' => 'use_config_qty_increments',
            'qty_increments' => 'qty_increments',
        ];
        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItemDoMock);

        $this->stockItemDoMock->expects($this->any())->method('getItemId')->willReturn(50);
        $this->stockItemDoMock->expects($this->any())
            ->method('getUseConfigEnableQtyInc')
            ->willReturn('use_config_enable_qty_inc');
        $this->stockItemDoMock->expects($this->any())
            ->method('getEnableQtyIncrements')
            ->willReturn('enable_qty_increments');
        $this->stockItemDoMock->expects($this->any())
            ->method('getUseConfigQtyIncrements')
            ->willReturn('use_config_qty_increments');
        $this->stockItemDoMock->expects($this->any())
            ->method('getQtyIncrements')
            ->willReturn('qty_increments');

        $this->duplicateMock->expects($this->once())->method('setStockData')->with($expectedData);
        $this->model->build($this->productMock, $this->duplicateMock);
    }
}
