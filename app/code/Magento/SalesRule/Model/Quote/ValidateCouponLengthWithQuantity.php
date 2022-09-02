<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Quote;

use Magento\SalesRule\Model\Coupon\Massgenerator;
use Magento\SalesRule\Helper\Coupon;

/**
 * Validate the coupon code length and quantity.
 */
class ValidateCouponLengthWithQuantity implements ValidateCouponLengthWithQuantityInterface
{
    /**
     * Sales rule coupon
     *
     * @var Coupon
     */
    protected Coupon $salesRuleCoupon;

    /**
    * @param Coupon $salesRuleCoupon
     */
    public function __construct(
        Coupon $salesRuleCoupon
    ) {
        $this->salesRuleCoupon = $salesRuleCoupon;
    }

    public function validateCouponCodeLengthWithQuantity(array $couponCodeDataArray): int
    {
        $maxProbability = Massgenerator::MAX_PROBABILITY_OF_GUESSING;
        $chars = count($this->salesRuleCoupon->getCharset($couponCodeDataArray['format']));
        $size = $couponCodeDataArray['qty'];
        $length = (int)$couponCodeDataArray['length'];
        $maxCodes = pow($chars, $length);
        $probability = $size / $maxCodes;

        if ($probability > $maxProbability) {
            do {
                $length++;
                $maxCodes = pow($chars, $length);
                $probability = $size / $maxCodes;
            } while ($probability > $maxProbability);
        }
        return $length;
    }
}
