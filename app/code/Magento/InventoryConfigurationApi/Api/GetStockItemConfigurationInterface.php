<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api;

/**
 * Returns stock item configuration data by sku and stock id.
 *
 * @api
 */
interface GetStockItemConfigurationInterface
{
    /**
     * @param string $sku
     * @param int $stockId
     * @return \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     */
    public function execute(
        string $sku,
        int $stockId
    ): \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
}
