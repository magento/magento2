<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Inventory\Ui\StockDataProvider;

use Magento\Inventory\Ui\DataProvider\StockDataProvider;

/**
 * Customize stock form. Add sales channels data
 */
class SalesChannels
{
    /**
     * @param StockDataProvider $subject
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(StockDataProvider $subject, array $data): array
    {
        foreach ($data as $stockId => $stockData) {
            $data[$stockId]['sales_channels'] = $this->getSalesChannelsDataForStock();
        }
        return $data;
    }

    /**
     * @return array
     */
    private function getSalesChannelsDataForStock(): array
    {
        // @todo: replace on real data
        return [
            'websites' => ['base'],
        ];
    }
}
