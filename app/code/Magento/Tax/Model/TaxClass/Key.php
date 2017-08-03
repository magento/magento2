<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\TaxClassKeyInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Key extends AbstractExtensibleModel implements TaxClassKeyInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_TYPE  = 'type';
    const KEY_VALUE = 'value';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->getData(self::KEY_VALUE);
    }

    /**
     * Set type of tax class key
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * Set value of tax class key
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value)
    {
        return $this->setData(self::KEY_VALUE, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxClassKeyExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxClassKeyExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
