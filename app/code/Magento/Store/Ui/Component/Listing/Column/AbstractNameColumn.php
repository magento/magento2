<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Ui\Component\Listing\Column;

/**
 * Class AbstractNameColumn
 */
abstract class AbstractNameColumn extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $this->prepareTitle($item);
                }
            }
        }

        return $dataSource;
    }

    abstract public function prepareTitle(array $item);
}
