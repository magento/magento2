<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

/**
 * Class FieldMapper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FieldType
{
    const ES_DATA_TYPE_STRING = 'string';
    const ES_DATA_TYPE_FLOAT = 'float';
    const ES_DATA_TYPE_INT = 'integer';
    const ES_DATA_TYPE_DATE = 'date';

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return string
     */
    public function getFieldType($attribute)
    {
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        if ($backendType === 'timestamp' || $backendType === 'datetime') {
            $fieldType = self::ES_DATA_TYPE_DATE;
        } elseif ($backendType === 'int' || $backendType === 'smallint') {
            $fieldType = self::ES_DATA_TYPE_INT;
        } elseif ($backendType === 'decimal') {
            $fieldType = self::ES_DATA_TYPE_FLOAT;
        } elseif ($backendType === 'varchar') {
            $fieldType = self::ES_DATA_TYPE_STRING;
        } elseif (in_array($frontendInput, ['multiselect', 'select', 'boolean'], true)) {
            $fieldType = self::ES_DATA_TYPE_INT;
        } else {
            $fieldType = self::ES_DATA_TYPE_STRING;
        }
        return $fieldType;
    }
}