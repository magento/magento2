<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

/**
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface ReplaceSalesChannelsOnStockInterface
{
    /**
     * @param array $salesChannels
     * @param int $stockId
     * @return void
     */
    public function execute(array $salesChannels, int $stockId);
}
