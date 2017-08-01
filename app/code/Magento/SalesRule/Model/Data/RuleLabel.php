<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Data;

/**
 * Class Rule label
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class RuleLabel extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\SalesRule\Api\Data\RuleLabelInterface
{
    const KEY_STORE_ID = 'store_id';
    const KEY_STORE_LABEL = 'store_label';

    /**
     * Get storeId
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId()
    {
        return $this->_get(self::KEY_STORE_ID);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::KEY_STORE_ID, $storeId);
    }

    /**
     * Return the label for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getStoreLabel()
    {
        return $this->_get(self::KEY_STORE_LABEL);
    }

    /**
     * Set the label for the store
     *
     * @param string $storeLabel
     * @return $this
     * @since 2.0.0
     */
    public function setStoreLabel($storeLabel)
    {
        return $this->setData(self::KEY_STORE_LABEL, $storeLabel);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\SalesRule\Api\Data\RuleLabelExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\SalesRule\Api\Data\RuleLabelExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\RuleLabelExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
