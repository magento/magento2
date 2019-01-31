<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestModule3\Service\V1\Entity;

class WrappedErrorParameter extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Get field name.
     *
     * @return string $name
     */
    public function getFieldName()
    {
        return $this->_data['field_name'];
    }

    /**
     * Get value.
     *
     * @return string $value
     */
    public function getValue()
    {
        return $this->_data['value'];
    }

    /**
     * Set field name.
     *
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName($fieldName)
    {
        return $this->setData('field_name', $fieldName);
    }

    /**
     * Set value.
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setData('value', $value);
    }
}
