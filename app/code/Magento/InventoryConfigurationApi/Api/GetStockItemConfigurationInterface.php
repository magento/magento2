<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;

/**
 * Returns stock item configuration data
 */
interface GetStockItemConfigurationInterface
{
    /**
     * @param string $sku
     * @param int $stockId
     * @return StockItemConfigurationInterface
     */
    public function execute(string $sku, int $stockId): StockItemConfigurationInterface;
}
