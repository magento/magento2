<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api;

/**
 * Interface for ExportStockData provides product stock information by search criteria
 * @api
 */
interface ExportStockDataInterface
{
    /**
     * Provides stock export data
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int $stockId
     * @return \Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface
     */
    public function execute(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        int $stockId
    ): \Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface;
}
