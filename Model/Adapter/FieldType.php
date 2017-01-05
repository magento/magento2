<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Class FieldType
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

    /** @deprecated */
    const ES_DATA_TYPE_ARRAY = 'array';
    /**#@-*/

    /**
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function getFieldType($attribute)
    {
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        if (in_array($backendType, ['timestamp', 'datetime'], true)) {
            $fieldType = self::ES_DATA_TYPE_DATE;
        } elseif (
            (in_array($backendType, ['int', 'smallint'], true)
            || (in_array($frontendInput, ['select', 'boolean'], true) && $backendType !== 'varchar'))
            && !$attribute->getIsUserDefined()
        ) {
            $fieldType = self::ES_DATA_TYPE_INT;
        } elseif ($backendType === 'decimal') {
            $fieldType = self::ES_DATA_TYPE_FLOAT;
        } else {
            $fieldType = self::ES_DATA_TYPE_STRING;
        }

        return $fieldType;
    }
}
