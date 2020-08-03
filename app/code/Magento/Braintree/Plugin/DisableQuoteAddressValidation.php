<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Plugin;

use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\Quote;

/**
 * Plugin for QuoteManagement to disable quote address validation
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class DisableQuoteAddressValidation
{
    /**
     * Disable quote address validation before submit order
     *
     * @param QuoteManagement $subject
     * @param Quote $quote
     * @param array $orderData
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSubmit(
        QuoteManagement $subject,
        Quote $quote,
        $orderData = []
    ) {
        if ($quote->getPayment()->getMethod() == 'braintree_paypal' &&
            (!$quote->getCustomer() || !$quote->getCustomer()->getAddresses())
        ) {
            $billingAddress = $quote->getBillingAddress();
            $billingAddress->setShouldIgnoreValidation(true);
            $quote->setBillingAddress($billingAddress);
        }
        return [$quote, $orderData];
    }
}
