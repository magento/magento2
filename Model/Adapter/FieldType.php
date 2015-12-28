<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

/**
 * Class FieldMapper
 */
class FieldType
{
    /**#@+
     * Text flags for Elasticsearch field types
     */
    const ES_DATA_TYPE_STRING = 'string';
    const ES_DATA_TYPE_FLOAT = 'float';
    const ES_DATA_TYPE_INT = 'integer';
    const ES_DATA_TYPE_DATE = 'date';
    const ES_DATA_TYPE_ARRAY = 'array';
    const ES_DATA_TYPE_NESTED = 'nested';
    const ES_DATA_TYPE_OBJECT = 'object';
    /**#@-*/

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return string
     */
    public function getFieldType($attribute)
    {
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();
        $attributeCode = $attribute->getAttributeCode();

        if ($attributeCode === 'price' || $attributeCode === 'media_gallery') {
            $fieldType = self::ES_DATA_TYPE_NESTED;
        } elseif ($attributeCode === 'quantity_and_stock_status' || $attributeCode === 'tier_price') {
            $fieldType = self::ES_DATA_TYPE_OBJECT;
        } elseif ($backendType === 'timestamp' || $backendType === 'datetime') {
            $fieldType = self::ES_DATA_TYPE_DATE;
        } elseif ($backendType === 'int' || $backendType === 'smallint') {
            $fieldType = self::ES_DATA_TYPE_INT;
        } elseif ($backendType === 'decimal') {
            $fieldType = self::ES_DATA_TYPE_FLOAT;
        } elseif ($backendType === 'varchar') {
            $fieldType = self::ES_DATA_TYPE_STRING;
        } elseif (in_array($frontendInput, ['select', 'boolean'], true)) {
            $fieldType = self::ES_DATA_TYPE_INT;
        } elseif ($frontendInput === 'multiselect') {
            $fieldType = self::ES_DATA_TYPE_ARRAY;
        } else {
            $fieldType = self::ES_DATA_TYPE_STRING;
        }

        return $fieldType;
    }
}
