<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface;

/**
 * Interface for ExportStockData
 * @api
 */
interface ExportStockDataInterface
{
    /**
     * Provides stock export data
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param int $qtyForNotManageStock
     * @return ExportStockDataSearchResultInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria, int $qtyForNotManageStock): ExportStockDataSearchResultInterface;
}
