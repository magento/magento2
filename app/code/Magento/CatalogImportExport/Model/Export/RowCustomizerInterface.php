<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;

/**
 * Interface RowCustomizerInterface
 *
 * @api
 * @since 2.0.0
 */
interface RowCustomizerInterface
{
    /**
     * Prepare data for export
     *
     * @param mixed $collection
     * @param int[] $productIds
     * @return mixed
     * @since 2.0.0
     */
    public function prepareData($collection, $productIds);

    /**
     * Set headers columns
     *
     * @param array $columns
     * @return mixed
     * @since 2.0.0
     */
    public function addHeaderColumns($columns);

    /**
     * Add data for export
     *
     * @param array $dataRow
     * @param int $productId
     * @return mixed
     * @since 2.0.0
     */
    public function addData($dataRow, $productId);

    /**
     * Calculate the largest links block
     *
     * @param array $additionalRowsCount
     * @param int $productId
     * @return mixed
     * @since 2.0.0
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId);
}
