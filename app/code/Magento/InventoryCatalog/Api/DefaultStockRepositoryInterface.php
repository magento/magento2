<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Api;

use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Represents default stock
 *
 * @api
 */
interface DefaultStockRepositoryInterface
{
    const DEFAULT_STOCK = 1;

    /**
     * Get default stock
     *
     * @return StockInterface
     */
    public function get(): StockInterface;
}