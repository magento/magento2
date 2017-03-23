<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule5\Service\V1\Entity;

/**
 * Some Data Object short description.
 *
 * Data Object long
 * multi line description.
 */
class AllSoapAndRest extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Entity ID
     */
    const ID = 'entity_id';
    const NAME = 'name';

    /**
     * Is Enabled
     */
    const ENABLED = 'enabled';
    const HAS_ORDERS = 'orders';

    /**
     * Retrieve item ID.
     *
     * @return int Item ID
     */
    public function getEntityId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set item ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Retrieve item Name.
     *
     * @return string|null Item name
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Set item Name.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Check if entity is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_get(self::ENABLED);
    }

    /**
     * Set if entity is enabled
     *
     * @param bool $isEnabled
     * @return bool
     */
    public function setIsEnabled($isEnabled)
    {
        return $this->setData(self::ENABLED, $isEnabled);
    }

    /**
     * Check if current entity has a property defined
     *
     * @return bool
     */
    public function hasOrders()
    {
        return $this->_get(self::HAS_ORDERS);
    }

    /**
     * Set whether current entity has a property defined
     *
     * @param bool $setHasOrders
     * @return $this
     */
    public function setHasOrders($hasOrders)
    {
        return $this->setData(self::HAS_ORDERS, $hasOrders);
    }

    /**
     * Method which will not be used when adding complex type field to WSDL.
     *
     * @param string $value
     * @return string
     */
    public function getFieldExcludedFromWsdl($value)
    {
        return $value;
    }
}
