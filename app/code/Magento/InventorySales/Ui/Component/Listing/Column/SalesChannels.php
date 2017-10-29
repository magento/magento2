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
     * @return array
     */
    private function prepareSalesChannelData(array $salesChannelData): array
    {
        return $salesChannelData;
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
                $row['sales_channels'] = $this->prepareSalesChannelData($row['sales_channels']);
            }
        }
        unset($row);

        return $dataSource;
    }
}
