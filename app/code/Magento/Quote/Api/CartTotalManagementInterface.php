<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Bundled API to collect totals for cart based on shipping/payment methods and additional data.
 * @api
 * @since 2.0.0
 */
interface CartTotalManagementInterface
{
    /**
     * Set shipping/billing methods and additional data for cart and collect totals.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Quote\Api\Data\PaymentInterface Payment method data.
     * @param string $shippingCarrierCode The carrier code.
     * @param string $shippingMethodCode The shipping method code.
     * @param \Magento\Quote\Api\Data\TotalsAdditionalDataInterface $additionalData Additional data to collect totals.
     * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     * @since 2.0.0
     */
    public function collectTotals(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        $shippingCarrierCode = null,
        $shippingMethodCode = null,
        \Magento\Quote\Api\Data\TotalsAdditionalDataInterface $additionalData = null
    );
}
