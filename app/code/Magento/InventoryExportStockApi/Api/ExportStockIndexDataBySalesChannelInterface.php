<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api;

interface ExportStockIndexDataBySalesChannelInterface
{
    /**
     * Provides stock index export from inventory_stock_% table by Sales Channel
     *
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
     * @return \Magento\InventoryExportStockApi\Api\Data\ProductStockIndexDataInterface[]
     */
    public function execute(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel): array;
}
