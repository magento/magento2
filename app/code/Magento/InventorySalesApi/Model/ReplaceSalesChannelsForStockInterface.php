<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Model;

/**
 * Implementation of links replacement between Stock and Sales Channels (Service Provider Interface - SPI)
 * Provide own implementation of this interface if you would like to replace channels management strategy
 *
 * @api
 */
interface ReplaceSalesChannelsForStockInterface
{
    /**
     * Replace Sales Channels for Stock
     *
     * @param array $salesChannels
     * @param int $stockId
     * @return void
     */
    public function execute(array $salesChannels, int $stockId): void;
}
