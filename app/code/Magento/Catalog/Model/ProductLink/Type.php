<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\Data\ProductLinkTypeInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Type extends \Magento\Framework\Api\AbstractExtensibleObject implements ProductLinkTypeInterface
{
    /**#@+
     * Constants
     */
    const KEY_CODE = 'code';
    const KEY_NAME = 'name';
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
    public function getName()
    {
        return $this->_get(self::KEY_NAME);
    }

    /**
     * Set link type code
     *
     * @param int $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Set link type name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
