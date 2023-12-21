<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
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
        ($observer->getOrder() || $observer->getOrders()) ? $this->updateCouponUsages->execute($quote, true)
            : $this->updateCouponUsages->execute($quote, false);
    }
}
