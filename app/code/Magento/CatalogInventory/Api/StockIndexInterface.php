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
 * @deprecated CatalogInventory will be replaced by Multi-Source Inventory (MSI)
 *             see https://devdocs.magento.com/guides/v2.3/rest/modules/inventory/inventory.html
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
