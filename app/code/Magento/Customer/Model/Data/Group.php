<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Set tax class id
     *
     * @param int $taxClassId
     * @return $this
     */
    public function setTaxClassId($taxClassId)
    {
        return $this->setData(self::TAX_CLASS_ID, $taxClassId);
    }

    /**
     * Set tax class name
     *
     * @param string $taxClassName
     * @return string|null
     */
    public function setTaxClassName($taxClassName)
    {
        return $this->setData(self::TAX_CLASS_NAME, $taxClassName);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Customer\Api\Data\GroupExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Customer\Api\Data\GroupExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\GroupExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
