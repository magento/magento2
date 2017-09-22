<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Stock\Command;

/**
 * Determine product quantity is in stock command (Service Provider Interface - SPI)
 *
 * You can extend and implement this command interface to customize current behaviour, but you are NOT expected to use
 * (call) it in the code of business logic directly.
 *
 * @see \Magento\InventoryApi\Api\GetProductQuantityInStockInterface
 * @api
 */
interface GetProductQuantityInterface
{
    /**
     * Get Product Quantity for given SKU in a given Stock
     *
     * @param string $sku
     * @param int $stockId
     * @return float
     */
    public function execute(string $sku, int $stockId): float;
}