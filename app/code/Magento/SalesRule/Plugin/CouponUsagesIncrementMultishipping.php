<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Closure;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderDefault;
use Throwable;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Increments number of coupon usages before placing order
 */
class CouponUsagesIncrementMultishipping
{

    /**
     * @var UpdateCouponUsages
     */
    private UpdateCouponUsages $updateCouponUsages;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepositoryInterface;

    /**
     * @param UpdateCouponUsages $updateCouponUsages
     * @param CartRepositoryInterface $cartRepositoryInterface
     */
    public function __construct(
        UpdateCouponUsages $updateCouponUsages,
        CartRepositoryInterface $cartRepositoryInterface
    ) {
        $this->updateCouponUsages = $updateCouponUsages;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
    }

    /**
     * Increments number of coupon usages before placing order
     *
     * @param PlaceOrderDefault $subject
     * @param Closure $proceed
     * @param array $order
     * @return array
     * @throws NoSuchEntityException|Throwable
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPlace(PlaceOrderDefault $subject, Closure $proceed, array $order): array
    {
        $quoteId = $order[0]->getQuoteId();
        $quote = $this->cartRepositoryInterface->get($quoteId);
        /* if coupon code has been canceled then need to notify the customer */
        if (!$quote->getCouponCode() && $quote->dataHasChangedFor('coupon_code')) {
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }

        $this->updateCouponUsages->execute($quote, true);
        try {
            return $proceed($order);
        } catch (Throwable $e) {
            $this->updateCouponUsages->execute($quote, false);
            throw $e;
        }
    }
}
