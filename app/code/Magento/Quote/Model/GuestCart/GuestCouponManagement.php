<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestCouponManagementInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Coupon management class for guest carts.
 * @since 2.0.0
 */
class GuestCouponManagement implements GuestCouponManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     * @since 2.0.0
     */
    private $quoteIdMaskFactory;

    /**
     * @var CouponManagementInterface
     * @since 2.0.0
     */
    private $couponManagement;

    /**
     * Constructs a coupon read service object.
     *
     * @param CouponManagementInterface $couponManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @since 2.0.0
     */
    public function __construct(
        CouponManagementInterface $couponManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->couponManagement = $couponManagement;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->couponManagement->get($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function set($cartId, $couponCode)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->couponManagement->set($quoteIdMask->getQuoteId(), $couponCode);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function remove($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->couponManagement->remove($quoteIdMask->getQuoteId());
    }
}
