<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Quote\Api\Data\ShippingInterface;

/**
 * Class Shipping
 */
class Shipping extends \Magento\Framework\Model\AbstractExtensibleModel implements ShippingInterface
{
    const ADDRESS = 'address';
    const METHOD = 'method';

    /**
     * @inheritDoc
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setAddress(\Magento\Quote\Api\Data\AddressInterface $value)
    {
        $this->setData(self::ADDRESS, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->getData(self::METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setMethod($value)
    {
        $this->setData(self::METHOD, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\ShippingExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
