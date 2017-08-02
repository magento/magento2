<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api\Data;

/**
 * Interface PaymentDetailsInterface
 * @api
 * @since 2.0.0
 */
interface PaymentDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const PAYMENT_METHODS = 'payment_methods';

    const TOTALS = 'totals';

    /**#@-*/

    /**
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[]
     * @since 2.0.0
     */
    public function getPaymentMethods();

    /**
     * @param \Magento\Quote\Api\Data\PaymentMethodInterface[] $paymentMethods
     * @return $this
     * @since 2.0.0
     */
    public function setPaymentMethods($paymentMethods);

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface
     * @since 2.0.0
     */
    public function getTotals();

    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     * @return $this
     * @since 2.0.0
     */
    public function setTotals($totals);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface $extensionAttributes
    );
}
