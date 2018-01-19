<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Framework\DB\Select;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AddIsInStockFilterToCollection;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockStatusToSelect;
use Magento\Store\Model\Website;

/**
 * Adapt Resource Model Stock Status for Multi-Source Inventory.
 */
class AdaptResourceModelStockStatusToMultiStocks
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $stockIdForCurrentWebsite;

    /**
     * @var AddStockStatusToSelect
     */
    private $adaptedAddStockStatusToSelect;

    /**
     * @var AddStockDataToCollection
     */
    private $adaptedAddStockDataToCollection;

    /**
     * @var AddIsInStockFilterToCollection
     */
    private $adaptedAddIsInStockFilterToCollection;

    /**
     * @param GetStockIdForCurrentWebsite $stockIdForCurrentWebsite
     * @param AddStockStatusToSelect $adaptedAddStockStatusToSelect
     * @param AddStockDataToCollection $adaptedAddStockDataToCollection
     * @param AddIsInStockFilterToCollection $adaptedAddIsInStockFilterToCollection
     */
    public function __construct(
        GetStockIdForCurrentWebsite $stockIdForCurrentWebsite,
        AddStockStatusToSelect $adaptedAddStockStatusToSelect,
        AddStockDataToCollection $adaptedAddStockDataToCollection,
        AddIsInStockFilterToCollection $adaptedAddIsInStockFilterToCollection
    ) {
        $this->stockIdForCurrentWebsite = $stockIdForCurrentWebsite;
        $this->adaptedAddStockStatusToSelect = $adaptedAddStockStatusToSelect;
        $this->adaptedAddStockDataToCollection = $adaptedAddStockDataToCollection;
        $this->adaptedAddIsInStockFilterToCollection = $adaptedAddIsInStockFilterToCollection;
    }

    /**
     * Adapt addStockStatusToSelect method.
     *
     * @param Status $stockStatus
     * @param callable $proceed
     * @param Select $select
     * @param Website $website
     * @return Status
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddStockStatusToSelect(
        Status $stockStatus,
        callable $proceed,
        Select $select,
        Website $website
    ) {
        $stockId = $this->stockIdForCurrentWebsite->execute();
        $this->adaptedAddStockStatusToSelect->addStockStatusToSelect($select, $stockId);

        return $stockStatus;
    }

    /**
     * Adapt addStockDataToCollection method.
     *
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
        $this->adaptedAddStockDataToCollection->addStockDataToCollection($collection, (bool)$isFilterInStock, $stockId);

        return $collection;
    }

    /**
     * Adapt addIsInStockFilterToCollection method.
     *
     * @param Status $stockStatus
     * @param callable $proceed
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return Status
     */
    public function aroundAddIsInStockFilterToCollection(
        Status $stockStatus,
        callable $proceed,
        $collection
    ) {
        $stockId = $this->stockIdForCurrentWebsite->execute();
        $this->adaptedAddIsInStockFilterToCollection->addIsInStockFilterToCollection($collection, $stockId);

        return $stockStatus;
    }
}
