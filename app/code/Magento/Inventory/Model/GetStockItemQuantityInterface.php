<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

/**
 * Responsible for retrieving StockItem Quantity (without reservation data)
 *
 * @api
 */
interface GetStockItemQuantityInterface
{
    /**
     * Given a product sku and a stock id, return stock item quantity
     *
     * @param string $sku
     * @param int $stockId
     * @return float
     */
    public function execute(string $sku, int $stockId): float;
}
