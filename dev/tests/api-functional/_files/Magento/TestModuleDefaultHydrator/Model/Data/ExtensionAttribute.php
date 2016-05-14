<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleDefaultHydrator\Model\Data;

class ExtensionAttribute extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\TestModuleDefaultHydrator\Api\Data\ExtensionAttributeInterface
{
    /**
     * Get id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get customer id
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set value
     *
     * @param int $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
