<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * Get prioritised sources with products quantity
 *
 * @api
 */
interface GetPrioritisedSourcesProductsQuantityInterface
{
    /**
     * Method return array sources id with quantity per source
     * @param string $productSku
     * @param int $count
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($productSku = "", $count = 1): array;
}