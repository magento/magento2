<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Model\Spi\StockResolverInterface;

/**
 * Class StockResolver
 */
class StockResolver implements StockResolverInterface
{
    const DEFAULT_STOCK_ID = 1;

    /**
     * @inheritdoc
     */
    public function getStockId($productId, $websiteId)
    {
        $stockId = self::DEFAULT_STOCK_ID;
        return $stockId;
    }
}
