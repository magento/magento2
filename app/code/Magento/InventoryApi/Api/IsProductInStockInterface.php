<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * Service which detects whether Product is In Stock for a given Stock (stock data + reservations)
 *
 * @api
 */
interface IsProductInStockInterface
{
    /**
     * Get Product Quantity for given SKU in a given Stock
     *
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function execute(string $sku, int $stockId): bool;
}
