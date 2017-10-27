<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Add grid column for sales channels
 */
class SalesChannels extends Column
{
    /**
     * Prepare column value
     *
     * @param array $salesChannelData
     * @return string
     */
    private function prepareStockChannelData(array $salesChannelData)
    {
        $websiteData = '';
        foreach ($salesChannelData as $key => $channelData) {
            $websiteData .= $key . ': ' . implode(',', $channelData);
        }
        return $websiteData;
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['sales_channels'] = $this->prepareStockChannelData($row['sales_channels']);
            }
        }
        unset($row);

        return $dataSource;
    }
}
