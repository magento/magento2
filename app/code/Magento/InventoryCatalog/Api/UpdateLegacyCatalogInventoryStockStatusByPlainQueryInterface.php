<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Api;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;

/**
 * Update Legacy catalocinventory_stock_status database data
 *
 * @api
 */
interface UpdateLegacyCatalogInventoryStockStatusByPlainQueryInterface
{
    /**
     * Execute Plain MySql query on catalaginventory_stock_status
     *
     * @param StockStatusInterface $stockStatus
     *
     * @return void
     */
    public function execute(StockStatusInterface $stockStatus);
}
