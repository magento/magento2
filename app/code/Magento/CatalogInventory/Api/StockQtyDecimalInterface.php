<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Api;

/**
 *  StockQtyDecimalInterface
 */
interface StockQtyDecimalInterface
{
    /**
     * Check whenever product qty is decimal
     *
     * @param int $productId
     * @param string|int|null $scopeId
     *
     * @return bool
     */
    public function isStockQtyDecimal(int $productId, $scopeId = null): bool;
}
