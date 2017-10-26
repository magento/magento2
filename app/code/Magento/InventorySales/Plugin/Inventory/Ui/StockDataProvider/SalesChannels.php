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
     */
    public function afterGetData(StockDataProvider $subject, array $data): array
    {
        if ('inventory_stock_form_data_source' === $subject->getName()) {
            foreach ($data as $stockId => &$stockData) {
                $stockData['sales_channels'] = $this->getSalesChannelsDataForStock();
            }
            unset($stockData);
        } elseif ($data['totalRecords'] > 0) {
            foreach ($data['items'] as $key => &$stockData) {
                $stockData['sales_channels'] = $this->getSalesChannelsDataForStock();
            }
            unset($stockData);
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
