<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\Data\ProductLinkAttributeInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Attribute extends \Magento\Framework\Api\AbstractExtensibleObject implements ProductLinkAttributeInterface
{
    /**#@+
     * Constants
     */
    const KEY_CODE = 'code';
    const KEY_TYPE = 'type';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->_get(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->_get(self::KEY_TYPE);
    }

    /**
     * Set attribute code
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
     * Set attribute type
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
     * {@inheritdoc}
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
