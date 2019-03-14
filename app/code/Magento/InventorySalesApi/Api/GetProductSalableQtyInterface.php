<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

/**
 * Service which returns Quantity of products available to be sold by Product SKU and Stock Id.
 * This service calculates the salable qty taking into account existing reservations for
 * given sku and stock id and subtracting min qty (a.k.a. "Out-of-Stock Threshold")
 *
 * @api
 */
interface GetProductSalableQtyInterface
{
    /**
     * Get Product Quantity for given SKU and Stock
     *
     * @param string $sku
     * @param int $stockId
     * @return float
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $sku, int $stockId): float;
}
