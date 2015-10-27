<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Cart Management class for guest carts.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestCartManagement implements GuestCartManagementInterface
{
    /**
     * @var CartManagementInterface
     */
    protected $quoteManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * Initialize dependencies.
     *
     * @param CartManagementInterface $quoteManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory
     * @param CartRepositoryInterface $cartRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CartManagementInterface $quoteManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        CartRepositoryInterface $cartRepository
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createEmptyCart()
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $cartId = $this->quoteManagement->createEmptyCart();

        /** @var \Magento\Quote\Model\Quote  $cart */
        $cart = $this->cartRepository->get($cartId);
        $billingAddress = $this->quoteAddressFactory->create()->setAddressType(Address::TYPE_BILLING);
        $cart->addAddress($billingAddress);
        $shippingAddress = $this->quoteAddressFactory->create()->setAddressType(Address::TYPE_SHIPPING);
        $cart->addAddress($shippingAddress);
        $cart->save();

        $quoteIdMask->setQuoteId($cartId)->save();
        return $quoteIdMask->getMaskedId();
    }

    /**
     * {@inheritdoc}
     */
    public function assignCustomer($cartId, $customerId, $storeId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->quoteManagement->assignCustomer($quoteIdMask->getQuoteId(), $customerId, $storeId);
    }

    /**
     * {@inheritdoc}
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
