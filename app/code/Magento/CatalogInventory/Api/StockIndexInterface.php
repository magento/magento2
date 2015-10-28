<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
