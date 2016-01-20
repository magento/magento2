<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Spi;

/**
 * Interface StockResolverInterface
 */
interface StockResolverInterface
{
    /**
     * @param int $productId
     * @param int $websiteId
     * @return int
     */
    public function getStockId($productId, $websiteId);
}
