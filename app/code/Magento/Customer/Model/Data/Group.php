<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Model\Data;

/**
 * Customer Group data model.
 */
class Group extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Customer\Api\Data\GroupInterface
{
    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * Get tax class ID
     *
     * @return int
     */
    public function getTaxClassId()
    {
        return $this->_get(self::TAX_CLASS_ID);
    }

    /**
     * Get tax class name
     *
     * @return string
     */
    public function getTaxClassName()
    {
        return $this->_get(self::TAX_CLASS_NAME);
    }
}
