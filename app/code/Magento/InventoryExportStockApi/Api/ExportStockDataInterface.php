<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api;

/**
 * Interface for ExportStockData
 * @api
 */
interface ExportStockDataInterface
{
    /**
     * Provides stock export data
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int $stockId
     * @param int $qtyForNotManageStock
     * @return \Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface
     */
    public function execute(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        int $stockId = null,
        int $qtyForNotManageStock = 1
    ): \Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface;
}
