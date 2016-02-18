<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        return $this->_getData(static::KEY_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->_getData(static::KEY_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        return $this->_getData(static::KEY_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddress($address)
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
    public function setTotal($total)
    {
        return $this->setData(self::KEY_TOTAL, $total);
    }
}
