<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model\Export;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

/**
 * Class \Magento\GroupedImportExport\Model\Export\RowCustomizer
 *
 */
class RowCustomizer implements RowCustomizerInterface
{
    /**
     * Prepare data for export
     *
     * @param mixed $collection
     * @param int $productIds
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareData($collection, $productIds)
    {
        return;
    }

    /**
     * Set headers columns
     *
     * @param array $columns
     * @return mixed
     */
    public function addHeaderColumns($columns)
    {
        $columns = array_merge(
            $columns,
            [
                'associated_skus'
            ]
        );
        return $columns;
    }

    /**
     * Add data for export
     *
     * @param array $dataRow
     * @param int $productId
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addData($dataRow, $productId)
    {
        return $dataRow;
    }

    /**
     * Calculate the largest links block
     *
     * @param array $additionalRowsCount
     * @param int $productId
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
