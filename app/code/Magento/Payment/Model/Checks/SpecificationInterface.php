<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Payment\Model\Checks;

use Magento\Quote\Model\Quote;

/**
 * Specification checks interface
 */
interface SpecificationInterface
{
    /**
     * Check whether payment method is applicable to quote
     *
     * @param PaymentMethodChecksInterface $paymentMethod
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(PaymentMethodChecksInterface $paymentMethod, Quote $quote);
}
