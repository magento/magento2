<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\PaymentMethodIntegration;

use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Provides mechanism to create string presentation of token for payment method.
 * Each payment method have own format of token details so rendering should be implemented by payment method.
 *
 * May implement any logic specific for a payment method and configured with
 * instant_purchase/tokenFormat configuration option in vault payment config.
 *
 * @api
 * @since 100.2.0
 */
interface PaymentTokenFormatterInterface
{
    /**
     * Creates string presentation of payment token.
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     * @since 100.2.0
     */
    public function formatPaymentToken(PaymentTokenInterface $paymentToken): string;
}
