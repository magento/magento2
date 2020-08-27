<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Export;

use Magento\CatalogImportExport\Model\Export\Product;

/**
 * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
 */
class StubProduct extends Product
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
        return parent::updateDataWithCategoryColumns($dataRow, $rowCategories, $productId);
    }
}
