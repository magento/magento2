<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api;

/**
 * Export product's salable qty information by search criteria
 * @api
 */
interface ExportStockSalableQtyBySalesChannelInterface
{
    /**
     * Export product stock data filtered by search criteria.
     *
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Data\ExportStockSalableQtySearchResultInterface
     */
    public function execute(
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterface;
}
