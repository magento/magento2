<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\MediaGalleryCatalogUi\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class InMenu column for Category grid
 */
class InMenu extends Column
{
    /**
     * Prepare data source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName]) && $item[$fieldName] == 1) {
                    $item[$fieldName] = 'Yes';
                } else {
                    $item[$fieldName] = 'No';
                }
            }
        }

        return $dataSource;
    }
}
