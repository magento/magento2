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
 *
 * @api
 */
interface GetStockItemConfigurationInterface
{
    /**
     * Return null if configuration for sku per stock is not exist
     *
     * @param string $sku
     * @param int $stockId
     * @return StockItemConfigurationInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $sku, int $stockId);
}
