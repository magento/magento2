<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Ui\Component\Listing\Columns;

class Quantity extends \Magento\Ui\Component\Listing\Columns\Column {

    const NAME = 'column.qty';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = (int) $item[$fieldName];
                }
            }
        }
        return $dataSource;
    }

}
