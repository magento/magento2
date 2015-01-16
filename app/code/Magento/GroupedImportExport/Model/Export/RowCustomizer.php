<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model\Export;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class RowCustomizer implements RowCustomizerInterface
{
    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        return;
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        $columns = array_merge(
            $columns,
            [
                '_associated_sku',
                '_associated_default_qty',
                '_associated_position'
            ]
        );
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        return $dataRow;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
