<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api;

/**
 * Class ExportStockIndexData provides stock index export based on raw data contained in the stock index.
 * @api
 */
interface ExportStockIndexDataInterface
{
    /**
     * Provides stock index export from inventory_stock_% table
     *
     * @param string $salesChannelType
     * @param string $salesChannelCode
     * @return \Magento\InventoryExportStockApi\Api\Data\ProductStockIndexDataInterface[]
     */
    public function execute(string $salesChannelType, string $salesChannelCode): array;
}
