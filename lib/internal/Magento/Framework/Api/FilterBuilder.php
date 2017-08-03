<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Builder for Filter Service Data Object.
 *
 * @api
 * @method Filter create()
 * @since 2.0.0
 */
class FilterBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * Set field
     *
     * @param string $field
     * @return $this
     * @since 2.0.0
     */
    public function setField($field)
    {
        $this->data['field'] = $field;
        return $this;
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
        $this->data['value'] = $value;
        return $this;
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
        $this->data['condition_type'] = $conditionType;
        return $this;
    }
}
