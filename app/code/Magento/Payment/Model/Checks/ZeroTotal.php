<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class ZeroTotal implements SpecificationInterface
{
    /**
     * Check whether payment method is applicable to quote
     * Purposed to allow use in controllers some logic that was implemented in blocks only before
     *
     * @param MethodInterface $paymentMethod
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        return !($quote->getBaseGrandTotal() < 0.0001 && $paymentMethod->getCode() != 'free');
    }
}
