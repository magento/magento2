<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Quote\Item;

use Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class ConfigurableItemOptionValue
 */
class ConfigurableItemOptionValue extends AbstractExtensibleModel implements ConfigurableItemOptionValueInterface
{
    //@codeCoverageIgnoreStart
    /**
     * {@inheritdoc}
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionId($value)
    {
        return $this->setData(self::OPTION_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionValue()
    {
        return $this->getData(self::OPTION_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionValue($value)
    {
        return $this->setData(self::OPTION_VALUE, $value);
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
