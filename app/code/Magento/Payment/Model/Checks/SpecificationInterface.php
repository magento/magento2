<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

/**
 * Specification checks interface
 *
 * @api
 * @since 2.0.0
 */
interface SpecificationInterface
{
    /**
     * Check whether payment method is applicable to quote
     *
     * @param MethodInterface $paymentMethod
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     * @since 2.0.0
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote);
}
