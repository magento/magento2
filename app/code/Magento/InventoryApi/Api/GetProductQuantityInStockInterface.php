<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * Service which returns Quantity of products available to be sold by Product SKU and Stock Id
 *
 * @api
 */
interface GetProductQuantityInStockInterface
{
    /**
     * Get Product Quantity for given SKU and Stock
     *
     * @param string $sku
     * @param int $stockId
     * @return float
     */
    public function execute(string $sku, int $stockId): float;
}
