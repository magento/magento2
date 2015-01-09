<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Filter which can be used by any methods from service layer.
 */
class Filter extends AbstractExtensibleObject
{
    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->_get('field');
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_get('value');
    }

    /**
     * Get condition type
     *
     * @return string|null
     */
    public function getConditionType()
    {
        return $this->_get('condition_type');
    }
}
