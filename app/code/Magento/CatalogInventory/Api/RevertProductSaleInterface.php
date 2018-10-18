<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Api;

/**
 * @api
 *
 * @deprecated CatalogInventory will be replaced by Multi-Source Inventory (MSI)
 *             see https://devdocs.magento.com/guides/v2.3/rest/modules/inventory/inventory.html
 */
interface RevertProductSaleInterface
{
    /**
     * Revert register product sale
     *
     * Method signature is unchanged for backward compatibility
     *
     * @param string[] $items
     * @param int $websiteId
     * @return bool
     */
    public function revertProductsSale($items, $websiteId = null);
}
