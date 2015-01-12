<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;

class StubProduct extends \Magento\CatalogImportExport\Model\Export\Product
{
    /**
     * Disable parent constructor
     */
    public function __construct()
    {
    }

    /**
     * Update data row with information about categories. Return true, if data row was updated
     *
     * @param array $dataRow
     * @param array $rowCategories
     * @param int $productId
     * @return bool
     */
    public function updateDataWithCategoryColumns(&$dataRow, &$rowCategories, $productId)
    {
        return $this->_updateDataWithCategoryColumns($dataRow, $rowCategories, $productId);
    }
}
