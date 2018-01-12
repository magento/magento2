<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\InventoryApi\Api\StockIndexTableProviderInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AdaptedAddStockDataToCollection;

/**
 * Adapt adding stock data to collection for Multi-Source Inventory.
 */
class AdaptAddStockDataToCollectionToMsi
{
    /**
     * @var StockIndexTableProviderInterface
     */
    private $stockIndexTableProvider;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $stockIdForCurrentWebsite;

    /**
     * @var AdaptedAddStockDataToCollection
     */
    private $adaptedAddStockDataToCollection;

    /**
     * @param StockIndexTableProviderInterface $stockIndexTableProvider
     * @param GetStockIdForCurrentWebsite $stockIdForCurrentWebsite
     * @param AdaptedAddStockDataToCollection $addStockDataToCollection
     */
    public function __construct(
        StockIndexTableProviderInterface $stockIndexTableProvider,
        GetStockIdForCurrentWebsite $stockIdForCurrentWebsite,
        AdaptedAddStockDataToCollection $addStockDataToCollection
    ) {
        $this->stockIndexTableProvider = $stockIndexTableProvider;
        $this->stockIdForCurrentWebsite = $stockIdForCurrentWebsite;
        $this->adaptedAddStockDataToCollection = $addStockDataToCollection;
    }

    /**
     *
     * @param Status $stockStatus
     * @param callable $proceed
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isFilterInStock
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function aroundAddStockDataToCollection(
        Status $stockStatus,
        callable $proceed,
        $collection,
        $isFilterInStock
    ) {
        $stockId = $this->stockIdForCurrentWebsite->execute();
        $tableName = $this->stockIndexTableProvider->execute($stockId);

        $this->adaptedAddStockDataToCollection->addStockDataToCollection($collection, $isFilterInStock, $tableName);

        return $collection;
    }
}
