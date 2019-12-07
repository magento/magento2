<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\InstantPurchase\Payflow\Pro;

use Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Stored credit card formatter.
 */
class TokenFormatter implements PaymentTokenFormatterInterface
{
    /**
     * Most used credit card types
     * @var array
     */
    public static $baseCardTypes = [
        'AE' => 'American Express',
        'VI' => 'Visa',
        'MC' => 'MasterCard',
        'DI' => 'Discover',
        'JBC' => 'JBC',
        'CUP' => 'China Union Pay',
        'MI' => 'Maestro',
    ];

    /**
     * @inheritdoc
     */
    public function formatPaymentToken(PaymentTokenInterface $paymentToken): string
    {
        $details = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        if (!isset($details['cc_type'], $details['cc_last_4'], $details['cc_exp_month'], $details['cc_exp_year'])) {
            throw new \InvalidArgumentException('Invalid PayPal Payflow Pro credit card token details.');
        }

        if (isset(self::$baseCardTypes[$details['cc_type']])) {
            $ccType = self::$baseCardTypes[$details['cc_type']];
        } else {
            $ccType = $details['cc_type'];
        }

        $formatted = sprintf(
            '%s: %s, %s: %s (%s: %02d/%04d)',
            __('Credit Card'),
            $ccType,
            __('ending'),
            $details['cc_last_4'],
            __('expires'),
            $details['cc_exp_month'],
            $details['cc_exp_year']
        );

        return $formatted;
    }
}
