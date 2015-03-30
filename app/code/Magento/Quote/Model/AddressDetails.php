<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

class AddressDetails extends \Magento\Framework\Model\AbstractExtensibleModel
    implements \Magento\Quote\Api\Data\AddressDetailsInterface
{
    /**
     * @{inheritdoc}
     */
    public function getShippingMethods()
    {
        return $this->getData('shipping_methods');
    }

    /**
     * @{inheritdoc}
     */
    public function getPaymentMethods()
    {
        return $this->getData('payment_methods');
    }

    /**
     * @{inheritdoc}
     */
    public function setShippingMethods($shippingMethods)
    {
        return $this->setData('shipping_methods', $shippingMethods);
    }

    /**
     * @{inheritdoc}
     */
    public function setPaymentMethods($paymentMethods)
    {
        return $this->setData('payment_methods', $paymentMethods);
    }
}
