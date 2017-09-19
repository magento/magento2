<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

/**
 * Checks is order total in allowed range or not
 *
 * @api
 * @since 100.0.2
 */
class TotalMinMax implements SpecificationInterface
{
    /**
     * Config value key for min order total
     */
    const MIN_ORDER_TOTAL = 'min_order_total';

    /**
     * Config value key for max order total
     */
    const MAX_ORDER_TOTAL = 'max_order_total';

    /**
     * Check whether payment method is applicable to quote
     *
     * @param MethodInterface $paymentMethod
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        $total = $quote->getBaseGrandTotal();
        $minTotal = $paymentMethod->getConfigData(self::MIN_ORDER_TOTAL);
        $maxTotal = $paymentMethod->getConfigData(self::MAX_ORDER_TOTAL);
        if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
            return false;
        }
        return true;
    }
}
