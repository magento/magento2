<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages;

/**
 * Decrement number of coupon usages after error of placing order
 */
class CouponUsagesDecrement implements ObserverInterface
{
    /**
     * @param UpdateCouponUsages $updateCouponUsages
     */
    public function __construct(
        private readonly UpdateCouponUsages $updateCouponUsages
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(EventObserver $observer)
    {
        /** @var CartInterface $quote */
        $quote = $observer->getQuote();
        $this->updateCouponUsages->execute($quote, false);
    }
}
