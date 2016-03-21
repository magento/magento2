<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\ShippingInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Shipping extends AbstractExtensibleModel implements ShippingInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return $this->_getData(self::KEY_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->_getData(self::KEY_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        return $this->_getData(self::KEY_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddress(\Magento\Sales\Api\Data\OrderAddressInterface $address)
    {
        return $this->setData(self::KEY_ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method)
    {
        return $this->setData(self::KEY_METHOD, $method);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotal(\Magento\Sales\Api\Data\TotalInterface $total)
    {
        return $this->setData(self::KEY_TOTAL, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShippingExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
