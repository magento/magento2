<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;

class CouponUsagesIncrement
{
    /**
     * @var UpdateCouponUsages
     */
    private $updateCouponUsages;

    public function __construct(
        UpdateCouponUsages $updateCouponUsages
    ) {
        $this->updateCouponUsages = $updateCouponUsages;
    }

    /**
     * Increments number of coupon usages after placing order.
     *
     * @param Order $subject
     * @param Order $result
     * @return Order
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(Order $subject, Order $result)
    {
        $this->updateCouponUsages->execute($subject, true);

        return $subject;
    }
}
