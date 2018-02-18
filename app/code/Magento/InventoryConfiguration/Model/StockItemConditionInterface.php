<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

/**
 * Interface StockConditionInterface
 * @package Magento\InventoryConfiguration\Model
 */
interface StockItemConditionInterface
{
    /**
     * @param string $sku
     * @param int $stockItem
     * @return bool
     */
    public function match(string $sku, int $stockItem): bool;
}
