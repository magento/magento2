<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

/**
 * @codeCoverageIgnoreStart
 */
class AddressDetails extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Quote\Api\Data\AddressDetailsInterface
{
    //@codeCoverageIgnoreStart
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

    /**
     * @{inheritdoc}
     */
    public function getFormattedShippingAddress()
    {
        return $this->getData(self::FORMATTED_SHIPPING_ADDRESS);
    }

    /**
     * @{inheritdoc}
     */
    public function getFormattedBillingAddress()
    {
        return $this->getData(self::FORMATTED_BILLING_ADDRESS);
    }

    /**
     * @{inheritdoc}
     */
    public function setFormattedBillingAddress($formattedBillingAddress)
    {
        return $this->setData(self::FORMATTED_BILLING_ADDRESS, $formattedBillingAddress);
    }

    /**
     * @{inheritdoc}
     */
    public function setFormattedShippingAddress($formattedShippingAddress)
    {
        return $this->setData(self::FORMATTED_SHIPPING_ADDRESS, $formattedShippingAddress);
    }

    /**
     * @{inheritdoc}
     */
    public function getTotals()
    {
        return $this->getData(self::TOTALS);
    }

    /**
     * @{inheritdoc}
     */
    public function setTotals($totals)
    {
        return $this->setData(self::TOTALS, $totals);
    }
    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Quote\Api\Data\AddressDetailsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Quote\Api\Data\AddressDetailsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\AddressDetailsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
