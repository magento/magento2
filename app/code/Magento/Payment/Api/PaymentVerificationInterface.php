<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Api;

use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Payment provider codes verification interface.
 *
 * Custom payment methods might implement this interface to provide
 * specific mapping for payment methods, like AVS or CVV verification.
 * The payment methods can map payment method info from internal sources,
 * like additional information, to specific international codes.
 *
 * @api
 */
interface PaymentVerificationInterface
{
    /**
     * Gets payment provider verification code.
     * Returns null if verification cannot be obtained by payment method.
     *
     * @param OrderPaymentInterface $orderPayment
     * @return string
     */
    public function getCode(OrderPaymentInterface $orderPayment);
}
