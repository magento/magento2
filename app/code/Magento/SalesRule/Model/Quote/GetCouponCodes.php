<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Quote;

use Magento\Quote\Api\Data\CartInterface;

class GetCouponCodes
{
    /**
     * Retrieve coupon data object
     *
     * @param CartInterface $quote
     * @return string[]
     */
    public function execute(CartInterface $quote): array
    {
        $couponCodes = [];
        if ($quote->getCouponCode()) {
            $couponCodes[] = $quote->getCouponCode();
        }
        return $couponCodes;
    }
}
