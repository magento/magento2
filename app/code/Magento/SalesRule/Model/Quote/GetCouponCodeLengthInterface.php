<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Quote;

/**
 * The class to get the coupon code length.
 */
interface GetCouponCodeLengthInterface
{
    /**
     * Fetch the coupon code length.
     *
     * @param array $couponCodeDataArray
     * @return int
     */
    public function fetchCouponCodeLength(array $couponCodeDataArray): int;
}
