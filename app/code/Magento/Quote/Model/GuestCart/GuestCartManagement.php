<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteManagement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestCartManagement
 */
class GuestCartManagement extends QuoteManagement implements GuestCartManagementInterface
{
    /**
     * @var GuestCartRepositoryInterface
     */
    protected $guestCartRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        GuestCartRepositoryInterface $guestCartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->guestCartRepository = $guestCartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @inheritdoc
     */
    public function createEmptyCart($customerId = null)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $cartId = parent::createEmptyCart($customerId);
        $quoteIdMask->setId($cartId)->save();
        return $quoteIdMask->getMaskedId();
    }

    /**
     * {@inheritdoc}
     */
    public function assignCustomer($cartId, $customerId, $storeId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->loadByMaskedId($cartId);
        return parent::assignCustomer($quoteIdMask->getId(), $customerId, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function placeOrder($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->loadByMaskedId($cartId);
        return parent::placeOrder($quoteIdMask->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getCartForCustomer($customerId)
    {
        $cart = $this->quoteRepository->getActiveForCustomer($customerId);
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->loadByMaskedId($cart->getId());
        $cart->setId($quoteIdMask->getId());
        return $cart;
    }
}
