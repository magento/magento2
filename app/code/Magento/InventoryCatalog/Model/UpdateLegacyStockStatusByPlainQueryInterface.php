<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

/**
 * Update Legacy catalocinventory_stock_status database data
 */
interface UpdateLegacyStockStatusByPlainQueryInterface
{
    /**
     * Execute Plain MySql query on catalaginventory_stock_status
     *
     * @param string $sku
     * @param float $quantity
     * @return void
     */
    public function execute(string $sku, float $quantity);
}
