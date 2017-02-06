<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Default implementation of codes verification interfaces.
 * Provides AVS, CVV codes matching for payment methods which are not support AVS, CVV verification.
 */
class NullPaymentVerification implements PaymentVerificationInterface
{
    /**
     * @inheritdoc
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        return null;
    }
}
