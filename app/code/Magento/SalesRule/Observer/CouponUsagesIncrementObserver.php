<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages;

/**
 * Decrement number of coupon usages after error of placing order
 */
class CouponUsagesIncrementObserver implements ObserverInterface
{
    /**
     * @var UpdateCouponUsages
     */
    private $updateCouponUsages;

    /**
     * @param UpdateCouponUsages $updateCouponUsages
     */
    public function __construct(UpdateCouponUsages $updateCouponUsages)
    {
        $this->updateCouponUsages = $updateCouponUsages;
    }

    /**
     * @inheritdoc
     */
    public function execute(EventObserver $observer)
    {
        /** @var CartInterface $quote */
        $quote = $observer->getQuote();
        /* if coupon code has been canceled then need to notify the customer */
        if (!$quote->getCouponCode() && $quote->dataHasChangedFor('coupon_code')) {
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }
        ($observer->getOrder()) ? $this->updateCouponUsages->execute($quote, true)
            : $this->updateCouponUsages->execute($quote, false);
    }
}
