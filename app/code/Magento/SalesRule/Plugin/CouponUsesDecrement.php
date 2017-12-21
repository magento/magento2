<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Model\Order;

class CouponUsesDecrement extends AbstractCouponUses
{
    /**
     * Decrements number of coupon uses after cancelling order.
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
            $returnValue = $this->updateCouponUses($returnValue, false);
        }

        return $returnValue;
    }
}
