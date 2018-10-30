<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api;

/**
 * Save stock item configuration data
 *
 * @api
 */
interface SaveStockItemConfigurationInterface
{
    /**
     * @param string $sku
     * @param int $stockId
     * @param \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface $stockItemConfiguration
     * @return void
     */
    public function execute(
        string $sku,
        int $stockId,
        \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface $stockItemConfiguration
    ): void;
}
