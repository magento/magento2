<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Update Legacy catalocinventory_stock_item database data
 */
interface UpdateCatalogInventoryStockItemByDefaultSourceItemInterface
{
    /**
     * Execute Plain MySql query on catalaginventory_stock_item
     *
     * @param SourceItemInterface $sourceItem
     * @return void
     */
    public function execute(SourceItemInterface $sourceItem);
}
