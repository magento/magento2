<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Framework\DB\Select;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\GetStockIdForWebsiteByCode;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockStatusToSelect;
use Magento\Store\Model\Website;

/**
 * Adapt adding stock status to select for multi stocks.
 */
class AdaptAddStockStatusToSelectToMultiStocks
{
    /**
     * @var GetStockIdForWebsiteByCode
     */
    private $getStockIdForWebsiteByCode;

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
     * @param GetStockIdForWebsiteByCode $getStockIdForWebsiteByCode
     * @param AddStockStatusToSelect $adaptedAddStockStatusToSelect
     */
    public function __construct(
        GetStockIdForCurrentWebsite $stockIdForCurrentWebsite,
        GetStockIdForWebsiteByCode $getStockIdForWebsiteByCode,
        AddStockStatusToSelect $adaptedAddStockStatusToSelect
    ) {
        $this->stockIdForCurrentWebsite = $stockIdForCurrentWebsite;
        $this->getStockIdForWebsiteByCode = $getStockIdForWebsiteByCode;
        $this->adaptedAddStockStatusToSelect = $adaptedAddStockStatusToSelect;
    }

    /**
     * @param Status $stockStatus
     * @param callable $proceed
     * @param Select $select
     * @param Website $website
     * @return Status
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddStockStatusToSelect(
        Status $stockStatus,
        callable $proceed,
        Select $select,
        Website $website
    ) {
        $websiteCode = $website->getCode();

        $stockId = $websiteCode === null
            ? $this->stockIdForCurrentWebsite->execute()
            : $this->getStockIdForWebsiteByCode->execute($websiteCode);

        $this->adaptedAddStockStatusToSelect->addStockStatusToSelect($select, $stockId);

        return $stockStatus;
    }
}
