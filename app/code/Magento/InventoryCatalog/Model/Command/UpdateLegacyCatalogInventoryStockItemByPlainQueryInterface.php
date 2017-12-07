<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Update Legacy catalocinventory_stock_item database data
 */
interface UpdateLegacyCatalogInventoryStockItemByPlainQueryInterface
{
    /**
     * Execute Plain MySql query on catalaginventory_stock_item
     *
     * @param StockItemInterface $stockItem
     *
     * @return void
     */
    public function execute(StockItemInterface $stockItem);
}
