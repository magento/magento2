<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api;

/**
 * Interface for ExportStockSalableQty provides product's salable qty information by search criteria
 * @api
 */
interface ExportStockSalableQtyInterface
{
    /**
     * Provides stock export data
     *
     * @param $salesChannelCode $salesChannelCode
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterface
     */
    public function execute(
        string $salesChannelCode,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterface;
}
