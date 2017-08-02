<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Filter which can be used by any methods from service layer.
 *
 * @api
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Filter extends AbstractSimpleObject
{
    /**#@+
     * Constants for Data Object keys
     */
    const KEY_FIELD = 'field';
    const KEY_VALUE = 'value';
    const KEY_CONDITION_TYPE = 'condition_type';

    /**
     * Get field
     *
     * @return string
     * @since 2.0.0
     */
    public function getField()
    {
        return $this->_get(self::KEY_FIELD);
    }

    /**
     * Set field
     *
     * @param string $field
     * @return $this
     * @since 2.0.0
     */
    public function setField($field)
    {
        return $this->setData(self::KEY_FIELD, $field);
    }

    /**
     * Get value
     *
     * @return string
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->_get(self::KEY_VALUE);
    }

    /**
     * Set value
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value)
    {
        return $this->setData(self::KEY_VALUE, $value);
    }

    /**
     * Get condition type
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getConditionType()
    {
        return $this->_get(self::KEY_CONDITION_TYPE) ?: 'eq';
    }

    /**
     * Set condition type
     *
     * @param string $conditionType
     * @return $this
     * @since 2.0.0
     */
    public function setConditionType($conditionType)
    {
        return $this->setData(self::KEY_CONDITION_TYPE, $conditionType);
    }
}
