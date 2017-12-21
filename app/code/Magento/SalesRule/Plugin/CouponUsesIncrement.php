<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Model\Order;

class CouponUsesIncrement extends AbstractCouponUses
{
    /**
     * Increments number of coupon uses after placing order.
     *
     * @param Order $subject
     * @param Order $result
     * @return Order
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(Order $subject, Order $result)
    {
        $this->updateCouponUses($subject, true);

        return $subject;
    }
}
