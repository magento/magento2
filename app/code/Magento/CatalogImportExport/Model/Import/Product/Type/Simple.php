<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Type;

/**
 * Import entity simple product type
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Simple extends \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Type virtual product
     */
    const TYPE_VIRTUAL_PRODUCT = 'virtual';

    /**
     * Attributes' codes which will be allowed anyway, independently from its visibility property.
     *
     * @var string[]
     */
    protected $_forcedAttributesCodes = [
        'related_tgtr_position_behavior',
        'related_tgtr_position_limit',
        'upsell_tgtr_position_behavior',
        'upsell_tgtr_position_limit',
        'thumbnail_label',
        'small_image_label',
        'image_label',
    ];

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
    protected function setWeightVirtualProduct(array $rowData){
        $result = [];
        if ($rowData['product_type'] == self::TYPE_VIRTUAL_PRODUCT){
            $result['weight'] = null;
        }
        return $result;
    }
}
