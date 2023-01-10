<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;

/**
 * Preload stock data into stock registry
 */
class StockRegistryPreloader
{
    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;
    /**
     * @var StockRegistryStorage
     */
    private $stockRegistryStorage;
    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;
    /**
     * @var StockStatusCriteriaInterfaceFactory
     */
    private $stockStatusCriteriaFactory;
    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryStorage $stockRegistryStorage
     */
    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        StockStatusRepositoryInterface $stockStatusRepository,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryStorage $stockRegistryStorage
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockStatusCriteriaFactory = $stockStatusCriteriaFactory;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistryStorage = $stockRegistryStorage;
    }

    /**
     * Preload stock item into stock registry
     *
     * @param array $productIds
     * @param int|null $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface[]
     */
    public function preloadStockItems(array $productIds, ?int $scopeId = null): array
    {
        $scopeId = $scopeId ?? $this->stockConfiguration->getDefaultScopeId();
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter($productIds);
        $criteria->setScopeFilter($scopeId);
        $collection = $this->stockItemRepository->getList($criteria);
        $this->setStockItems($collection->getItems(), $scopeId);
        return $collection->getItems();
    }

    /**
     * Saves stock items into registry
     *
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface[] $stockItems
     * @param int $scopeId
     */
    public function setStockItems(array $stockItems, int $scopeId): void
    {
        foreach ($stockItems as $item) {
            $this->stockRegistryStorage->setStockItem($item->getProductId(), $scopeId, $item);
        }
    }

    /**
     * Preload stock status into stock registry
     *
     * @param array $productIds
     * @param int|null $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface[]
     */
    public function preloadStockStatuses(array $productIds, ?int $scopeId = null): array
    {
        $scopeId = $scopeId ?? $this->stockConfiguration->getDefaultScopeId();
        $criteria = $this->stockStatusCriteriaFactory->create();
        $criteria->setProductsFilter($productIds);
        $criteria->setScopeFilter($scopeId);
        $collection = $this->stockStatusRepository->getList($criteria);
        $this->setStockStatuses($collection->getItems(), $scopeId);
        return $collection->getItems();
    }

    /**
     * Saves stock statuses into registry
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface[] $stockStatuses
     * @param int $scopeId
     */
    public function setStockStatuses(array $stockStatuses, int $scopeId): void
    {
        foreach ($stockStatuses as $item) {
            $this->stockRegistryStorage->setStockStatus($item->getProductId(), $scopeId, $item);
        }
    }
}
