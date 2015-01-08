<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
