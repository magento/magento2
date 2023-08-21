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
 * @since 100.0.2
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
     */
    public function setField($field)
    {
        return $this->setData(self::KEY_FIELD, $field);
    }

    /**
     * Get value
     *
     * @return string
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
     */
    public function setValue($value)
    {
        return $this->setData(self::KEY_VALUE, $value);
    }

    /**
     * Get condition type
     *
     * @return string|null
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
     */
    public function setConditionType($conditionType)
    {
        return $this->setData(self::KEY_CONDITION_TYPE, $conditionType);
    }
}
