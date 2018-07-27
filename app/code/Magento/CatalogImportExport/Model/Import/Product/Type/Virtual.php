<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Type;

/**
 * Import entity virtual product type
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Virtual extends \Magento\CatalogImportExport\Model\Import\Product\Type\Simple
{
    /**
     * Type virtual product
     */
    const TYPE_VIRTUAL_PRODUCT = 'virtual';

    /**
     * Prepare attributes with default value for save.
     *
     * @param array $rowData
     * @param bool $withDefaultValue
     * @return array
     */
    public function prepareAttributesWithDefaultValueForSave(array $rowData, $withDefaultValue = true)
    {
        $resultAttrs = parent::prepareAttributesWithDefaultValueForSave($rowData, $withDefaultValue);
        $resultAttrs = array_merge($resultAttrs, $this->setWeightVirtualProduct($rowData));
        return $resultAttrs;
    }

    /**
     * Set weight is null if product is virtual
     *
     * @param array $rowData
     * @return array
     */
    protected function setWeightVirtualProduct(array $rowData)
    {
        $result = [];
        if ($rowData['product_type'] == self::TYPE_VIRTUAL_PRODUCT) {
            $result['weight'] = null;
        }
        return $result;
    }
}
