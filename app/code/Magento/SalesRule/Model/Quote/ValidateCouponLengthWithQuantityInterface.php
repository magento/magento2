<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Quote;

/**
 * Validate the coupon code length and quantity.
 */
interface ValidateCouponLengthWithQuantityInterface
{
    /**
     * Validate coupon code length with quantity
     *
     * @param array $couponCodeDataArray
     * @return int
     */
    public function validateCouponCodeLengthWithQuantity(array $couponCodeDataArray): int;
}
