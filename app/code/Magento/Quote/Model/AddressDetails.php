<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

/**
 * @codeCoverageIgnoreStart
 */
class AddressDetails extends \Magento\Framework\Model\AbstractExtensibleModel
    implements \Magento\Quote\Api\Data\AddressDetailsInterface
{
    /**
     * @{inheritdoc}
     */
    public function getShippingMethods()
    {
        return $this->getData(self::SHIPPING_METHODS);
    }

    /**
     * @{inheritdoc}
     */
    public function setShippingMethods($shippingMethods)
    {
        return $this->setData(self::SHIPPING_METHODS, $shippingMethods);
    }

    /**
     * @{inheritdoc}
     */
    public function getPaymentMethods()
    {
        return $this->getData(self::PAYMENT_METHODS);
    }

    /**
     * @{inheritdoc}
     */
    public function setPaymentMethods($paymentMethods)
    {
        return $this->setData(self::PAYMENT_METHODS, $paymentMethods);
    }
}
