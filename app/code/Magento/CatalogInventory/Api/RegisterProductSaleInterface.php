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
 *
 * @deprecated 2.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.3/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.3/inventory/catalog-inventory-replacements.html
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
