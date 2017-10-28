<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

/**
 * TODO: SPI description
 *
 * @api
 */
interface ReplaceSalesChannelsOnStockInterface
{
    /**
     * Replace existing or non existing Sales Channels for Stock
     *
     * @param array $salesChannels
     * @param int $stockId
     * @return void
     */
    public function execute(array $salesChannels, int $stockId);
}
