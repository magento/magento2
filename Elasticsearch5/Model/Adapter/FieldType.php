<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Class FieldType
 * @api
 * @since 100.1.0
 */
class FieldType
{
    /**#@+
     * Text flags for Elasticsearch field types
     */
    const ES_DATA_TYPE_TEXT = 'text';
    const ES_DATA_TYPE_KEYWORD = 'keyword';
    const ES_DATA_TYPE_FLOAT = 'float';
    const ES_DATA_TYPE_INT = 'integer';
    const ES_DATA_TYPE_DATE = 'date';

    /** @deprecated */
    const ES_DATA_TYPE_ARRAY = 'array';
    /**#@-*/

    /**
     * @param AbstractAttribute $attribute
     * @return string
     * @since 100.1.0
     */
    public function getFieldType($attribute)
    {
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        if (in_array($backendType, ['timestamp', 'datetime'], true)) {
            $fieldType = self::ES_DATA_TYPE_DATE;
        } elseif ((in_array($backendType, ['int', 'smallint'], true)
            || (in_array($frontendInput, ['select', 'boolean'], true) && $backendType !== 'varchar'))
            && !$attribute->getIsUserDefined()
        ) {
            $fieldType = self::ES_DATA_TYPE_INT;
        } elseif ($backendType === 'decimal') {
            $fieldType = self::ES_DATA_TYPE_FLOAT;
        } else {
            $fieldType = self::ES_DATA_TYPE_TEXT;
        }

        return $fieldType;
    }
}
