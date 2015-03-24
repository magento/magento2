<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Magento\ConfigurableProduct\Api\Data\OptionValueInterface;

/**
 * Class OptionValue
 *
 */
class OptionValue extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\ConfigurableProduct\Api\Data\OptionValueInterface
{
    /**#@+
     * Constants for field names
     */
    const KEY_PRICING_VALUE = 'pricing_value';
    const KEY_IS_PERCENT = 'is_percent';
    const KEY_VALUE_INDEX = 'value_index';
    /**#@-*/

    //@codeCoverageIgnoreStart
    /**
     * {@inheritdoc}
     */
    public function getPricingValue()
    {
        return $this->getData(self::KEY_PRICING_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsPercent()
    {
        return $this->getData(self::KEY_IS_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getValueIndex()
    {
        return $this->getData(self::KEY_VALUE_INDEX);
    }

    /**
     * @param float $pricingValue
     * @return $this
     */
    public function setPricingValue($pricingValue)
    {
        return $this->setData(self::KEY_PRICING_VALUE, $pricingValue);
    }

    /**
     * @param int $isPercent
     * @return $this
     */
    public function setIsPercent($isPercent)
    {
        return $this->setData(self::KEY_IS_PERCENT, $isPercent);
    }

    /**
     * @param int $valueIndex
     * @return $this
     */
    public function setValueIndex($valueIndex)
    {
        return $this->setData(self::KEY_VALUE_INDEX, $valueIndex);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\ConfigurableProduct\Api\Data\OptionValueExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\OptionValueExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
