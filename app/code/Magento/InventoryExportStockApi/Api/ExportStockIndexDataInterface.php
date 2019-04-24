<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api;

/**
 * Interface for ExportStockIndexData which provides stock index export
 * @api
 */
interface ExportStockIndexDataInterface
{
    /**
     * Provides stock index export from inventory_stock_% table
     *
     * @param string $websiteCode
     * @return string[]
     */
    public function execute(string $websiteCode): array;
}
