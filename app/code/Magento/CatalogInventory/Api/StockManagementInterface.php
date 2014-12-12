<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockManagementInterface
 */
interface StockManagementInterface
{
    /**
     * Get back to stock (when order is canceled or whatever else)
     *
     * @param int $productId
     * @param float $qty
     * @param int $websiteId
     * @return bool
     */
    public function backItemQty($productId, $qty, $websiteId = null);
}
