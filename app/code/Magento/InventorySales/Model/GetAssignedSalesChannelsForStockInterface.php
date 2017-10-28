<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * TODO: describe SPI
 * Get assigned Sales Channels for Stock
 *
 * @api
 */
interface GetAssignedSalesChannelsForStockInterface
{
    /**
     * Get linked sales channels for Stock
     *
     * @param int $stockId
     * @return SalesChannelInterface[]
     */
    public function execute(int $stockId): array;
}
