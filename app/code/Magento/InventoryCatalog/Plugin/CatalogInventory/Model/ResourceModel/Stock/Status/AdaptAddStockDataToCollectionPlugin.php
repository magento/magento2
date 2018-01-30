<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection;

/**
 * Adapt adding stock data to collection for multi stocks.
 */
class AdaptAddStockDataToCollectionPlugin
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var AddStockDataToCollection
     */
    private $addStockDataToCollection;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param AddStockDataToCollection $addStockDataToCollection
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        AddStockDataToCollection $addStockDataToCollection
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->addStockDataToCollection = $addStockDataToCollection;
    }

    /**
     * @param Status $stockStatus
     * @param callable $proceed
     * @param Collection $collection
     * @param bool $isFilterInStock
     * @return Collection $collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddStockDataToCollection(
        Status $stockStatus,
        callable $proceed,
        $collection,
        $isFilterInStock
    ) {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $this->addStockDataToCollection->execute($collection, (bool)$isFilterInStock, $stockId);

        return $collection;
    }
}
