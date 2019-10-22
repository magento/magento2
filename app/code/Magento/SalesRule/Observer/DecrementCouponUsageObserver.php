<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesRule\Exception\CouponUsageExceeded;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;
use Throwable;

/**
 * Undo quote coupon usage update
 */
class DecrementCouponUsageObserver implements ObserverInterface
{
    /**
     * @var UpdateCouponUsages
     */
    private $updateCouponUsages;

    /**
     * Initializes dependencies
     *
     * @param UpdateCouponUsages $updateCouponUsages
     */
    public function __construct(UpdateCouponUsages $updateCouponUsages)
    {
        $this->updateCouponUsages = $updateCouponUsages;
    }

    /**
     * Forces related quotes to be recollected, if the rule was disabled or deleted.
     *
     * @param Observer $observer
     * @return void
     * @throws CouponUsageExceeded
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        /** @var OrderInterface $order */
        $order = $observer->getOrder();
        try {
            $this->updateCouponUsages->execute($order, false);
        } catch (CouponUsageExceeded|LocalizedException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw new LocalizedException(__('Unable to cancel coupon code.'));
        }
    }
}
