<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\InstantPurchase\PayPal;

use Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Braintree PayPal token formatter.
 */
class TokenFormatter implements PaymentTokenFormatterInterface
{
    /**
     * @inheritdoc
     */
    public function formatPaymentToken(PaymentTokenInterface $paymentToken): string
    {
        $details = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        if (!isset($details['payerEmail'])) {
            throw new \InvalidArgumentException('Invalid Braintree PayPal token details.');
        }

        $formatted = sprintf(
            '%s: %s',
            __('PayPal'),
            $details['payerEmail']
        );

        return $formatted;
    }
}
