<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api\Data;

/**
 * Get Stock Id and Sales Channel connection
 *
 * @api
 */
interface GetSalesChannelToStockDataInterface
{
    /**
     * @return array|null
     */
    public function execute();
}
