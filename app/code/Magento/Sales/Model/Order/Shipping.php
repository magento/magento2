<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\ShippingInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class \Magento\Sales\Model\Order\Shipping
 *
 * @since 2.1.0
 */
class Shipping extends AbstractExtensibleModel implements ShippingInterface
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getAddress()
    {
        return $this->_getData(self::KEY_ADDRESS);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getMethod()
    {
        return $this->_getData(self::KEY_METHOD);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getTotal()
    {
        return $this->_getData(self::KEY_TOTAL);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setAddress(\Magento\Sales\Api\Data\OrderAddressInterface $address)
    {
        return $this->setData(self::KEY_ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setMethod($method)
    {
        return $this->setData(self::KEY_METHOD, $method);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setTotal(\Magento\Sales\Api\Data\TotalInterface $total)
    {
        return $this->setData(self::KEY_TOTAL, $total);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShippingExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
