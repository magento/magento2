<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockStatusToSelect;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\Website;

/**
 * Adapt adding stock status to select for multi stocks.
 */
class AdaptAddStockStatusToSelectPlugin
{
    /**
     * @var StockResolverInterface
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var AddStockStatusToSelect
     */
    private $addStockStatusToSelect;

    /**
     * @param StockResolverInterface $getStockIdForCurrentWebsite
     * @param AddStockStatusToSelect $addStockStatusToSelect
     */
    public function __construct(
        StockResolverInterface $getStockIdForCurrentWebsite,
        AddStockStatusToSelect $addStockStatusToSelect
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->addStockStatusToSelect = $addStockStatusToSelect;
    }

    /**
     * @param Status $stockStatus
     * @param callable $proceed
     * @param Select $select
     * @param Website $website
     * @return Status
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddStockStatusToSelect(
        Status $stockStatus,
        callable $proceed,
        Select $select,
        Website $website
    ) {
        $websiteCode = $website->getCode();
        if (null === $websiteCode) {
            throw new LocalizedException(__('Website code is empty'));
        }

        $stock = $this->getStockIdForCurrentWebsite->get(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = (int)$stock->getStockId();

        $this->addStockStatusToSelect->execute($select, $stockId);

        return $stockStatus;
    }
}
