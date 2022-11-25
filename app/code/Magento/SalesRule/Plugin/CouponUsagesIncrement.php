<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages;

/**
 * Increments number of coupon usages before placing order
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
    public function __construct(UpdateCouponUsages $updateCouponUsages)
    {
        $this->updateCouponUsages = $updateCouponUsages;
    }

    /**
     * Increments number of coupon usages before placing order
     *
     * @param QuoteManagement $subject
     * @param \Closure $proceed
     * @param Quote $quote
     * @param array $orderData
     * @return OrderInterface|null
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSubmit(QuoteManagement $subject, \Closure $proceed, Quote $quote, $orderData = [])
    {
        /* if coupon code has been canceled then need to notify the customer */
        if (!$quote->getCouponCode() && $quote->dataHasChangedFor('coupon_code')) {
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }

        $this->updateCouponUsages->execute($quote, true);
        try {
            return $proceed($quote, $orderData);
        } catch (\Throwable $e) {
            $this->updateCouponUsages->execute($quote, false);
            throw $e;
        }
    }
}
