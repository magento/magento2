<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Quote;

use Magento\SalesRule\Model\Coupon\Massgenerator;

/**
 * Validate the coupon code length and quantity.
 */
class ValidateCouponLengthWithQuantity extends Massgenerator implements ValidateCouponLengthWithQuantityInterface
{
    /**
     * Validate coupon code length with quantity
     *
     * @param array $couponCodeDataArray
     * @return int
     */
    public function validateCouponCodeLengthWithQuantity(array $couponCodeDataArray): int
    {
        $this->setData($couponCodeDataArray);
        $this->increaseLength();
        return (int)$this->getLength();
    }
}
