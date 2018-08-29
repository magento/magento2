<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 */
interface RegisterProductSaleInterface
{
    /**
     * Subtract product qtys from stock
     * Return array of items that require full save
     *
     * Method signature is unchanged for backward compatibility
     *
     * @param float[] $items
     * @param int $websiteId
     * @return StockItemInterface[]
     * @throws LocalizedException
     */
    public function registerProductsSale($items, $websiteId = null);
}
