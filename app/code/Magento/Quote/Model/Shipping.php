<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Quote\Api\Data\ShippingInterface;

/**
 * Class Shipping
 * @since 2.0.0
 */
class Shipping extends \Magento\Framework\Model\AbstractExtensibleModel implements ShippingInterface
{
    const ADDRESS = 'address';
    const METHOD = 'method';

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setAddress(\Magento\Quote\Api\Data\AddressInterface $value)
    {
        $this->setData(self::ADDRESS, $value);
        return $this;
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getMethod()
    {
        return $this->getData(self::METHOD);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setMethod($value)
    {
        $this->setData(self::METHOD, $value);
        return $this;
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\ShippingExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
