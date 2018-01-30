<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AddIsInStockFilterToCollection;

/**
 * Adapt adding is in stock filter to collection for multi stocks.
 */
class AdaptAddIsInStockFilterToCollection
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var AddIsInStockFilterToCollection
     */
    private $adaptedAddIsInStockFilterToCollection;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param AddIsInStockFilterToCollection $adaptedAddIsInStockFilterToCollection
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        AddIsInStockFilterToCollection $adaptedAddIsInStockFilterToCollection
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->adaptedAddIsInStockFilterToCollection = $adaptedAddIsInStockFilterToCollection;
    }

    /**
     * @param Status $stockStatus
     * @param callable $proceed
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return Status
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddIsInStockFilterToCollection(
        Status $stockStatus,
        callable $proceed,
        $collection
    ) {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $this->adaptedAddIsInStockFilterToCollection->addIsInStockFilterToCollection($collection, $stockId);

        return $stockStatus;
    }
}
