<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Service\OrderService;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Exception\CouponUsageExceeded;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;

/**
 * Increments number of coupon usages after placing order.
 */
class CouponUsagesIncrement
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
     * Increments number of coupon usages after placing order.
     *
     * @param OrderService $subject
     * @param OrderInterface $order
     * @return array
     * @throws CouponUsageExceeded
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePlace(OrderService $subject, OrderInterface $order): array
    {
        $this->updateCouponUsages->execute($order, true);

        return [$order];
    }
}
