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
 * @api
 */
interface PaymentVerificationInterface
{
    /**
     * Gets payment provider verification code.
     * Returns null if payment method does not support verification.
     *
     * @return string|null
     */
    public function getCode(OrderPaymentInterface $orderPayment);
}
