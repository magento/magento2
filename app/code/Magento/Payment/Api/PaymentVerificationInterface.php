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
 * There are no default implementation of this interface, because code verification
 * depends on payment method integration specifics.
 *
 * @api
 */
interface PaymentVerificationInterface
{
    /**
     * Gets payment provider verification code.
     * Throws an exception if provided payment method is different to verification implementation.
     *
     * @param OrderPaymentInterface $orderPayment
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getCode(OrderPaymentInterface $orderPayment);
}
