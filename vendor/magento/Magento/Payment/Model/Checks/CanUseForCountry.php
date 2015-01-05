<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Payment\Model\Checks;

use Magento\Sales\Model\Quote;

class CanUseForCountry implements SpecificationInterface
{
    /**
     * Check whether payment method is applicable to quote
     * @param PaymentMethodChecksInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     */
    public function isApplicable(PaymentMethodChecksInterface $paymentMethod, Quote $quote)
    {
        return $paymentMethod->canUseForCountry($quote->getBillingAddress()->getCountry());
    }
}
