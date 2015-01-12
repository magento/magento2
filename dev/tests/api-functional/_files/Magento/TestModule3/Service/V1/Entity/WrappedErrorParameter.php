<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
}
