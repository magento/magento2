<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

/**
 * Service which returns linked stock for a certain sales channel
 *
 * @api
 */
interface GetStockBySalesChannelInterface
{
    /**
     * Resolve Stock by Sales Channel
     *
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     */
    public function execute(
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
    ): \Magento\InventoryApi\Api\Data\StockInterface;
}
