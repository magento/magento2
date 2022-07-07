<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockIndexInterface
 * @api
 * @since 100.0.2
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.4/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.4/inventory/inventory-api-reference.html
 */
interface StockIndexInterface
{
    /**
     * Rebuild stock index of the given scope
     *
     * @param int $productId
     * @param int $scopeId
     * @return bool
     */
    public function rebuild($productId = null, $scopeId = null);
}
