<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestModule3\Service\V1\Entity;

class WrappedErrorParameterBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * Set field name.
     *
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName($fieldName)
    {
        $this->data['field_name'] = $fieldName;
        return $this;
    }

    /**
     * Set value.
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->data['value'] = $value;
        return $this;
    }
}
