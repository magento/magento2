<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventorySales\Plugin\Inventory\Ui\DataProvider;

use Magento\Inventory\Ui\DataProvider\StockDataProvider;

class SalesChannelData
{
    /**
     * @param StockDataProvider $subject
     * @param $result
     * @return array
     */
    public function afterGetData(StockDataProvider $subject, array $result): array
    {
        // @todo: update to real data
        $result[1]['sales_channels']['websites'] = [1];

        return $result;
    }
}

