<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Data;

/**
 * Customer Group data model.
 * @since 2.0.0
 */
class Group extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Customer\Api\Data\GroupInterface
{
    /**
     * Get ID
     *
     * @return int
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * Get tax class ID
     *
     * @return int
     * @since 2.0.0
     */
    public function getTaxClassId()
    {
        return $this->_get(self::TAX_CLASS_ID);
    }

    /**
     * Get tax class name
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setTaxClassName($taxClassName)
    {
        return $this->setData(self::TAX_CLASS_NAME, $taxClassName);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Customer\Api\Data\GroupExtensionInterface|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\GroupExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
