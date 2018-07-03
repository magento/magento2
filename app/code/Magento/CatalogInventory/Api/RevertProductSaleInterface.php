<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Api;

/**
 * @api
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
