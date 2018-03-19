<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;

/**
 * Save stock item configuration data
 *
 * @api
 */
interface SaveStockItemConfigurationInterface
{
    /**
     * @param string $sku
     * @param StockItemConfigurationInterface $stockItemConfiguration
     * @return void
     */
    public function execute(string $sku, int $stockId, StockItemConfigurationInterface $stockItemConfiguration);
}
