<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Interface CartTotalManagementInterface
 *
 * Collect cart totals based on shipping and payment methods.
 */
interface CartTotalManagementInterface
{
    /**
     * Set shipping and billing methods for cart and collect totals.
     *
     * @param int $cartId The cart ID.
     * @param string $shippingCarrierCode The carrier code.
     * @param string $shippingMethodCode The shipping method code.
     * @param \Magento\Quote\Api\Data\PaymentInterface Payment method data.
     * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     */
    public function collectTotals(
        $cartId,
        $shippingCarrierCode = null,
        $shippingMethodCode = null,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    );
}
