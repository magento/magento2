<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

use Magento\Quote\Model\Quote;

/**
 * Extract data from payment
 */
class PaymentDataProvider
{
    /**
     * Extract data from cart
     *
     * @param Quote $cart
     * @return array
     */
    public function getCartPayment(Quote $cart): array
    {
        $payment = $cart->getPayment();
        if (!$payment) {
            return [];
        }

        return [
            'method' => $payment->getMethod(),
            'po_number' => $payment->getPoNumber(),
        ];
    }
}
