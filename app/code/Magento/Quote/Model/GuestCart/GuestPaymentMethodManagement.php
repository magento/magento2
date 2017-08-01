<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\GuestPaymentMethodManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Payment method management class for guest carts.
 * @since 2.0.0
 */
class GuestPaymentMethodManagement implements GuestPaymentMethodManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     * @since 2.0.0
     */
    protected $quoteIdMaskFactory;

    /**
     * @var PaymentMethodManagementInterface
     * @since 2.0.0
     */
    protected $paymentMethodManagement;

    /**
     * Initialize dependencies.
     *
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @since 2.0.0
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentMethodManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->paymentMethodManagement = $paymentMethodManagement;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function set($cartId, \Magento\Quote\Api\Data\PaymentInterface $method)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->paymentMethodManagement->set($quoteIdMask->getQuoteId(), $method);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->paymentMethodManagement->get($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getList($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->paymentMethodManagement->getList($quoteIdMask->getQuoteId());
    }
}
