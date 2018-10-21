<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;

class CouponUsagesDecrement
{
    /**
     * @var UpdateCouponUsages
     */
    private $updateCouponUsages;

    /**
     * CouponUsagesDecrement constructor.
     *
     * @param UpdateCouponUsages $updateCouponUsages
     */
    public function __construct(
        UpdateCouponUsages $updateCouponUsages
    ) {
        $this->updateCouponUsages = $updateCouponUsages;
    }

    /**
     * Decrements number of coupon usages after cancelling order.
     *
     * @param Order $subject
     * @param callable $proceed
     * @return Order
     */
    public function aroundCancel(Order $subject, callable $proceed)
    {
        $canCancel = $subject->canCancel();
        $returnValue = $proceed();
        if ($canCancel) {
            $returnValue = $this->updateCouponUsages->execute($returnValue, false);
        }

        return $returnValue;
    }
}
