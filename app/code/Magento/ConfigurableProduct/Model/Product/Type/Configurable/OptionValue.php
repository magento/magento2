<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class OptionValue
 *
 * @since 2.0.0
 */
class OptionValue extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\ConfigurableProduct\Api\Data\OptionValueInterface
{
    /**#@+
     * Constants for field names
     */
    const KEY_VALUE_INDEX = 'value_index';
    /**#@-*/

    //@codeCoverageIgnoreStart

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getValueIndex()
    {
        return $this->getData(self::KEY_VALUE_INDEX);
    }

    /**
     * @param int $valueIndex
     * @return $this
     * @since 2.0.0
     */
    public function setValueIndex($valueIndex)
    {
        return $this->setData(self::KEY_VALUE_INDEX, $valueIndex);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\ConfigurableProduct\Api\Data\OptionValueExtensionInterface|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\OptionValueExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
