<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Paypal\Helper;

use Magento\Quote\Model\Quote;

/**
 * Abstract class AbstractHelper
 * @since 2.1.0
 */
abstract class AbstractHelper
{
    /**
     * Make sure addresses will be saved without validation errors
     *
     * @param Quote $quote
     * @return void
     * @since 2.1.0
     */
    protected function disabledQuoteAddressValidation(Quote $quote)
    {
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setShouldIgnoreValidation(true);

        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setShouldIgnoreValidation(true);
            if (!$billingAddress->getEmail()) {
                $billingAddress->setSameAsBilling(1);
            }
        }
    }
}
