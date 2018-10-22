<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

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
     * @param SalesChannelInterface $salesChannel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return StockInterface
     */
    public function execute(SalesChannelInterface $salesChannel): StockInterface;
}
