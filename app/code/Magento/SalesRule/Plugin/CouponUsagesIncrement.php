<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon\QuantityManager;

class CouponUsagesIncrement
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
     * Increments number of coupon usages after placing order.
     *
     * @param Order $subject
     * @param Order $result
     * @return Order
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(Order $subject, Order $result)
    {
        $this->quantityManager->updateCouponUsages($subject, true);

        return $subject;
    }
}
