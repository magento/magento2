<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Quote\Item;

use Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class ConfigurableItemOptionValue
 * @since 2.0.0
 */
class ConfigurableItemOptionValue extends AbstractExtensibleModel implements ConfigurableItemOptionValueInterface
{
    //@codeCoverageIgnoreStart

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setOptionId($value)
    {
        return $this->setData(self::OPTION_ID, $value);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getOptionValue()
    {
        return $this->getData(self::OPTION_VALUE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setOptionValue($value)
    {
        return $this->setData(self::OPTION_VALUE, $value);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
