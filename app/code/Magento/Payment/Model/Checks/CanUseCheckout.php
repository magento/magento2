<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

/**
 * Checks possibility of payment method to be used in storefront
 *
 * @api
 * @since 2.0.0
 */
class CanUseCheckout implements SpecificationInterface
{
    /**
     * Check whether payment method is applicable to quote
     *
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     * @since 2.0.0
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        return $paymentMethod->canUseCheckout();
    }
}
