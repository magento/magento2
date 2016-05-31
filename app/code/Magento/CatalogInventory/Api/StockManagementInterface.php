<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockManagementInterface
 * @api
 */
interface StockManagementInterface
{
    /**
     * Get back to stock (when order is canceled or whatever else)
     *
     * @param int $productId
     * @param float $qty
     * @param int $scopeId
     * @return bool
     */
    public function backItemQty($productId, $qty, $scopeId = null);
}
