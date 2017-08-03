<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockIndexInterface
 * @api
 * @since 2.0.0
 */
interface StockIndexInterface
{
    /**
     * Rebuild stock index of the given scope
     *
     * @param int $productId
     * @param int $scopeId
     * @return bool
     * @since 2.0.0
     */
    public function rebuild($productId = null, $scopeId = null);
}
