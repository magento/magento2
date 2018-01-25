<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon\QuantityManager;

class CouponUsagesDecrement
{
    /**
     * @var QuantityManager
     */
    private $quantityManager;

    public function __construct(
        QuantityManager $quantityManager
    ) {
        $this->quantityManager = $quantityManager;
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
            $returnValue = $this->quantityManager->updateCouponUsages($returnValue, false);
        }

        return $returnValue;
    }
}
