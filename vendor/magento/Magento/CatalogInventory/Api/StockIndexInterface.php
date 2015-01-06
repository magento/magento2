<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockIndexInterface
 */
interface StockIndexInterface
{
    /**
     * Rebuild stock index of the given website
     *
     * @param int $productId
     * @param int $websiteId
     * @return bool
     */
    public function rebuild($productId = null, $websiteId = null);
}
