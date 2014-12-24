<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Payment\Model\Checks;

use Magento\Quote\Model\Quote;

class ZeroTotal implements SpecificationInterface
{
    /**
     * Check whether payment method is applicable to quote
     * Purposed to allow use in controllers some logic that was implemented in blocks only before
     *
     * @param PaymentMethodChecksInterface $paymentMethod
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(PaymentMethodChecksInterface $paymentMethod, Quote $quote)
    {
        return !($quote->getBaseGrandTotal() < 0.0001 && $paymentMethod->getCode() != 'free');
    }
}
