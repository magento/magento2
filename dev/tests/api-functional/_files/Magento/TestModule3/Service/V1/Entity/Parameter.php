<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule3\Service\V1\Entity;

class Parameter extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Get Name.
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->_data['name'];
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
