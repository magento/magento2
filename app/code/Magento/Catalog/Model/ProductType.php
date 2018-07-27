<?php
/**
 * Product type
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductTypeInterface;

/**
 * @codeCoverageIgnore
 */
class ProductType extends \Magento\Framework\Api\AbstractExtensibleObject implements ProductTypeInterface
{
    /**#@+
     * Constants
     */
    const KEY_NAME = 'name';
    const KEY_LABEL = 'label';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_get(self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->_get(self::KEY_LABEL);
    }

    /**
     * Set product type code
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * Set product type label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(self::KEY_LABEL, $label);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Catalog\Api\Data\ProductTypeExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Api\Data\ProductTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductTypeExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
