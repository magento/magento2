<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;

/**
 * Decrements number of coupon usages after cancelling order.
 */
class CouponUsagesDecrement
{
    /**
     * @var UpdateCouponUsages
     */
    private $updateCouponUsages;

    /**
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
    public function aroundCancel(Order $subject, callable $proceed): Order
    {
        $canCancel = $subject->canCancel();
        $returnValue = $proceed();
        if ($canCancel) {
            $returnValue = $this->updateCouponUsages->execute($returnValue, false);
        }

        return $returnValue;
    }
}
