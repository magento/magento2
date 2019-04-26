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
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param string $websiteCode
     * @return \Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterface
     */
    public function execute(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        string $websiteCode
    ): \Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterface;
}
