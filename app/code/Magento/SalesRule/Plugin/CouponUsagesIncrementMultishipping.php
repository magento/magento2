<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;
use Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderDefault;

/**
 * Increments number of coupon usages before placing order
 */
class CouponUsagesIncrementMultishipping
{

    /**
     * @var UpdateCouponUsages
     */
    private $updateCouponUsages;

    /**
     * @var QuoteRepository
     */
    private QuoteRepository $quoteRepository;

    /**
     * @param UpdateCouponUsages $updateCouponUsages
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(UpdateCouponUsages $updateCouponUsages, QuoteRepository $quoteRepository)
    {
        $this->updateCouponUsages = $updateCouponUsages;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Increments number of coupon usages before placing order
     *
     * @param PlaceOrderDefault $subject
     * @param \Closure $proceed
     * @param array $order
     * @return void
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPlace(PlaceOrderDefault $subject, \Closure $proceed, array $order)
    {
        $quoteId = $order[0]->getQuoteId();
        $quote = $this->quoteRepository->get($quoteId);
        /* if coupon code has been canceled then need to notify the customer */
        if (!$quote->getCouponCode() && $quote->dataHasChangedFor('coupon_code')) {
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }

        $this->updateCouponUsages->execute($quote, true);
        try {
            return $proceed($order);
        } catch (\Throwable $e) {
            $this->updateCouponUsages->execute($quote, false);
            throw $e;
        }
    }
}
