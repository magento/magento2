<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model;

use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\StockRegistryPreloader;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for StockRegistryStorage
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockRegistryPreloaderTest extends TestCase
{
    /**
     * @var StockItemRepositoryInterface|MockObject
     */
    private $stockItemRepository;
    /**
     * @var StockStatusRepositoryInterface|MockObject
     */
    private $stockStatusRepository;
    /**
     * @var MockObject
     */
    private $stockItemCriteriaFactory;
    /**
     * @var MockObject
     */
    private $stockStatusCriteriaFactory;
    /**
     * @var StockConfigurationInterface|MockObject
     */
    private $stockConfiguration;
    /**
     * @var StockRegistryStorage
     */
    private $stockRegistryStorage;
    /**
     * @var StockRegistryPreloader
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->stockItemRepository = $this->createMock(StockItemRepositoryInterface::class);
        $this->stockStatusRepository = $this->createMock(StockStatusRepositoryInterface::class);
        $this->stockItemCriteriaFactory = $this->createMock(StockItemCriteriaInterfaceFactory::class);
        $this->stockStatusCriteriaFactory = $this->createMock(StockStatusCriteriaInterfaceFactory::class);
        $this->stockConfiguration = $this->createMock(StockConfigurationInterface::class);
        $this->stockRegistryStorage = new StockRegistryStorage();
        $this->model = new StockRegistryPreloader(
            $this->stockItemRepository,
            $this->stockStatusRepository,
            $this->stockItemCriteriaFactory,
            $this->stockStatusCriteriaFactory,
            $this->stockConfiguration,
            $this->stockRegistryStorage,
        );
    }

    public function testPreloadStockItems(): void
    {
        $productIds = [10, 20];
        $scopeId = 1;
        $stockItems = [
            $this->createConfiguredMock(StockItemInterface::class, ['getProductId' => 10]),
            $this->createConfiguredMock(StockItemInterface::class, ['getProductId' => 20]),
        ];
        $collection = $this->createConfiguredMock(StockItemCollectionInterface::class, ['getItems' => $stockItems]);
        $criteria = $this->createMock(StockItemCriteriaInterface::class);
        $criteria->expects($this->once())
            ->method('setProductsFilter')
            ->with($productIds)
            ->willReturnSelf();
        $criteria->expects($this->once())
            ->method('setScopeFilter')
            ->with($scopeId)
            ->willReturnSelf();
        $this->stockItemRepository->method('getList')
            ->willReturn($collection);
        $this->stockItemCriteriaFactory->method('create')
            ->willReturn($criteria);
        $this->assertEquals($stockItems, $this->model->preloadStockItems($productIds, $scopeId));
        $this->assertSame($stockItems[0], $this->stockRegistryStorage->getStockItem(10, $scopeId));
        $this->assertSame($stockItems[1], $this->stockRegistryStorage->getStockItem(20, $scopeId));
    }

    public function testPreloadStockStatuses(): void
    {
        $productIds = [10, 20];
        $scopeId = 1;
        $stockItems = [
            $this->createConfiguredMock(StockStatusInterface::class, ['getProductId' => 10]),
            $this->createConfiguredMock(StockStatusInterface::class, ['getProductId' => 20]),
        ];
        $collection = $this->createConfiguredMock(StockStatusCollectionInterface::class, ['getItems' => $stockItems]);
        $criteria = $this->createMock(StockStatusCriteriaInterface::class);
        $criteria->expects($this->once())
            ->method('setProductsFilter')
            ->with($productIds)
            ->willReturnSelf();
        $criteria->expects($this->once())
            ->method('setScopeFilter')
            ->with($scopeId)
            ->willReturnSelf();
        $this->stockStatusRepository->method('getList')
            ->willReturn($collection);
        $this->stockStatusCriteriaFactory->method('create')
            ->willReturn($criteria);
        $this->assertEquals($stockItems, $this->model->preloadStockStatuses($productIds, $scopeId));
        $this->assertSame($stockItems[0], $this->stockRegistryStorage->getStockStatus(10, $scopeId));
        $this->assertSame($stockItems[1], $this->stockRegistryStorage->getStockStatus(20, $scopeId));
    }

    public function testSetStockItems(): void
    {
        $scopeId = 1;
        $stockItems = [
            $this->createConfiguredMock(StockItemInterface::class, ['getProductId' => 10]),
            $this->createConfiguredMock(StockItemInterface::class, ['getProductId' => 20]),
        ];
        $this->model->setStockItems($stockItems, $scopeId);
        $this->assertSame($stockItems[0], $this->stockRegistryStorage->getStockItem(10, $scopeId));
        $this->assertSame($stockItems[1], $this->stockRegistryStorage->getStockItem(20, $scopeId));
    }

    public function testSetStockStatuses(): void
    {
        $scopeId = 1;
        $stockItems = [
            $this->createConfiguredMock(StockStatusInterface::class, ['getProductId' => 10]),
            $this->createConfiguredMock(StockStatusInterface::class, ['getProductId' => 20]),
        ];
        $this->model->setStockStatuses($stockItems, $scopeId);
        $this->assertSame($stockItems[0], $this->stockRegistryStorage->getStockStatus(10, $scopeId));
        $this->assertSame($stockItems[1], $this->stockRegistryStorage->getStockStatus(20, $scopeId));
    }
}
