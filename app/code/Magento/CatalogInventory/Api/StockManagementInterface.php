<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockManagementInterface
 * @api
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function backItemQty($productId, $qty, $scopeId = null);
}
