<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Quote\Item;

class ConfigurableItemOptionValue extends \Magento\Framework\Model\AbstractExtensibleModel
    implements \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($value)
    {
        return $this->setData('sku', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->getData('item_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($value)
    {
        return $this->setData('item_id', $value);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
