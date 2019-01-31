<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface;

/**
 * Class FieldType
 *
 * @api
 * @since 100.1.0
 *
 * @deprecated This class provide not full data about field type. Only basic rules apply on this class.
 * @see ResolverInterface
 */
class FieldType
{
    /**#@+
     * @deprecated
     * @see \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
     *
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
     * Get field type.
     *
     * @deprecated
     * @see ResolverInterface::getFieldType
     *
     * @param AbstractAttribute $attribute
     * @return string
     * @since 100.1.0
     */
    public function getFieldType($attribute)
    {
        trigger_error('Class is deprecated', E_USER_DEPRECATED);
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        if ($backendType === 'timestamp') {
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
