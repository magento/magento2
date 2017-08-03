<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Cart Management class for guest carts.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class GuestCartManagement implements GuestCartManagementInterface
{
    /**
     * @var CartManagementInterface
     * @since 2.0.0
     */
    protected $quoteManagement;

    /**
     * @var QuoteIdMaskFactory
     * @since 2.0.0
     */
    protected $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     * @since 2.0.0
     */
    protected $cartRepository;

    /**
     * Initialize dependencies.
     *
     * @param CartManagementInterface $quoteManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        CartManagementInterface $quoteManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function createEmptyCart()
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $cartId = $this->quoteManagement->createEmptyCart();
        $quoteIdMask->setQuoteId($cartId)->save();
        return $quoteIdMask->getMaskedId();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function assignCustomer($cartId, $customerId, $storeId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->quoteManagement->assignCustomer($quoteIdMask->getQuoteId(), $customerId, $storeId);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function placeOrder($cartId, PaymentInterface $paymentMethod = null)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->cartRepository->get($quoteIdMask->getQuoteId())
            ->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
        return $this->quoteManagement->placeOrder($quoteIdMask->getQuoteId(), $paymentMethod);
    }
}
