<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

/**
 * Service which detects whether Product is salable for a given Stock (stock data + reservations)
 *
 * @api
 */
interface IsProductSalableInterface
{
    /**
     * Get is product in salable for given SKU in a given Stock
     *
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function execute(string $sku, int $stockId): bool;
}
