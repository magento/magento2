<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Ui\Component\Columns;

use Magento\Eav\Model\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Ui\DataProvider\AbstractDataProvider;

class ExportFilter extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!empty($dataSource['data']['items'])) {
            /** @var AbstractDataProvider $dataProvider */
            $dataProvider = $this->getContext()->getDataProvider();
            $collection = $dataProvider->getCollection();

            foreach ($dataSource['data']['items'] as &$item) {
                /** @var Attribute $attribute */
                $attribute = $collection->getItemById($item['attribute_id']);

                try {
                    $item['filterType'] = Export::getAttributeFilterType($attribute);
                } catch (LocalizedException $e) {
                    $item[$this->getName()] = $e->getMessage();
                }
            }
        }

        return $dataSource;
    }
}
