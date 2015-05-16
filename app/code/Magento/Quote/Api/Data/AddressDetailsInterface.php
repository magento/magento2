<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

interface AddressDetailsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const SHIPPING_METHODS = 'shipping_methods';

    const PAYMENT_METHODS = 'payment_methods';

    const FORMATTED_BILLING_ADDRESS = 'formatted_billing_address';

    const FORMATTED_SHIPPING_ADDRESS = 'formatted_shipping_address';

    const TOTALS = 'totals';

    /**#@-*/

    /**
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingMethods();

    /**
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[]
     */
    public function getPaymentMethods();

    /**
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface[] $shippingMethods
     * @return $this
     */
    public function setShippingMethods($shippingMethods);

    /**
     * @param \Magento\Quote\Api\Data\PaymentMethodInterface[] $paymentMethods
     * @return $this
     */
    public function setPaymentMethods($paymentMethods);

    /**
     * @return string|null
     */
    public function getFormattedShippingAddress();

    /**
     * @return string
     */
    public function getFormattedBillingAddress();

    /**
     * @param string $formattedBillingAddress
     * @return $this
     */
    public function setFormattedBillingAddress($formattedBillingAddress);

    /**
     * @param string $formattedShippingAddress
     * @return $this
     */
    public function setFormattedShippingAddress($formattedShippingAddress);


    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\AddressDetailsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\AddressDetailsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\AddressDetailsExtensionInterface $extensionAttributes
    );

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals();

    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     * @return $this
     */
    public function setTotals($totals);
}
