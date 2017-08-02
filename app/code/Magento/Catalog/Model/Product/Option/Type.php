<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Type extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Catalog\Api\Data\ProductCustomOptionTypeInterface
{
    /**#@+
     * Constants
     */
    const KEY_LABEL = 'label';
    const KEY_CODE = 'code';
    const KEY_GROUP = 'group';
    /**#@-*/

    /**
     * Get option type label
     *
     * @return string
     * @since 2.0.0
     */
    public function getLabel()
    {
        return $this->getData(self::KEY_LABEL);
    }

    /**
     * Get option type code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * Get option type group
     *
     * @return string
     * @since 2.0.0
     */
    public function getGroup()
    {
        return $this->getData(self::KEY_GROUP);
    }

    /**
     * Set option type label
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label)
    {
        return $this->setData(self::KEY_LABEL, $label);
    }

    /**
     * Set option type code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Set option type group
     *
     * @param string $group
     * @return $this
     * @since 2.0.0
     */
    public function setGroup($group)
    {
        return $this->setData(self::KEY_GROUP, $group);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
