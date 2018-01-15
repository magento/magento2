<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockStatusToSelect;

/**
 * Adapt adding stock status to select for Multi-Source Inventory.
 */
class AdaptAddStockStatusToSelectToMultiStocks
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
     * @param GetStockIdForCurrentWebsite $stockIdForCurrentWebsite
     * @param AddStockStatusToSelect $adaptedAddStockStatusToSelect
     */
    public function __construct(
        GetStockIdForCurrentWebsite $stockIdForCurrentWebsite,
        AddStockStatusToSelect $adaptedAddStockStatusToSelect
    ) {
        $this->stockIdForCurrentWebsite = $stockIdForCurrentWebsite;
        $this->adaptedAddStockStatusToSelect = $adaptedAddStockStatusToSelect;
    }

    /**
     * @param Status $stockStatus
     * @param callable $proceed
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Store\Model\Website $website
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return Status
     */
    public function aroundAddStockStatusToSelect(
        Status $stockStatus,
        callable $proceed,
        \Magento\Framework\DB\Select $select,
        \Magento\Store\Model\Website $website
    ) {
        $stockId = $this->stockIdForCurrentWebsite->execute();
        $this->adaptedAddStockStatusToSelect->addStockStatusToSelect($select, $stockId);

        return $stockStatus;
    }
}
