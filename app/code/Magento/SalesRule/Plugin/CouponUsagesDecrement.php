<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\OrderService;
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
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @param UpdateCouponUsages $updateCouponUsages
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        UpdateCouponUsages $updateCouponUsages,
        OrderRepository $orderRepository
    ) {
        $this->updateCouponUsages = $updateCouponUsages;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Decrements number of coupon usages after cancelling order.
     *
     * @param OrderService $subject
     * @param bool $result
     * @param int $orderId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCancel(OrderService $subject, bool $result, $orderId): bool
    {
        $order = $this->orderRepository->get($orderId);
        if ($result) {
            $this->updateCouponUsages->execute($order, false);
        }

        return $result;
    }
}
