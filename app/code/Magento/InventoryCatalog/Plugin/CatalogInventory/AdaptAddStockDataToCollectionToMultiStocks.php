<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection;

/**
 * Adapt adding stock data to collection for multi stocks.
 */
class AdaptAddStockDataToCollectionToMultiStocks
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $stockIdForCurrentWebsite;

    /**
     * @var AddStockDataToCollection
     */
    private $addStockDataToCollection;

    /**
     * @param GetStockIdForCurrentWebsite $stockIdForCurrentWebsite
     * @param AddStockDataToCollection $addStockDataToCollection
     */
    public function __construct(
        GetStockIdForCurrentWebsite $stockIdForCurrentWebsite,
        AddStockDataToCollection $addStockDataToCollection
    ) {
        $this->stockIdForCurrentWebsite = $stockIdForCurrentWebsite;
        $this->addStockDataToCollection = $addStockDataToCollection;
    }

    /**
     * @param Status $stockStatus
     * @param callable $proceed
     * @param Collection $collection
     * @param bool $isFilterInStock
     * @return Collection $collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddStockDataToCollection(
        Status $stockStatus,
        callable $proceed,
        $collection,
        $isFilterInStock
    ) {
        $stockId = $this->stockIdForCurrentWebsite->execute();
        $this->addStockDataToCollection->addStockDataToCollection($collection, (bool)$isFilterInStock, $stockId);

        return $collection;
    }
}
