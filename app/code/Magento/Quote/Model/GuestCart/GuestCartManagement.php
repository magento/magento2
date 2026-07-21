<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\GuestCart\GetGuestCart;
use Magento\Framework\App\ObjectManager;

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
     * @var GetGuestCart|null
     */
    private $getGuestCart;

    /**
     * Initialize dependencies.
     *
     * @param CartManagementInterface $quoteManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param GetGuestCart|null $getGuestCart
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CartManagementInterface $quoteManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        ?GetGuestCart $getGuestCart = null
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->getGuestCart = $getGuestCart ?? ObjectManager::getInstance()->get(GetGuestCart::class);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function assignCustomer($cartId, $customerId, $storeId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->quoteManagement->assignCustomer($quoteIdMask->getQuoteId(), $customerId, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function placeOrder($cartId, ?PaymentInterface $paymentMethod = null)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        $this->cartRepository->get($quoteIdMask->getQuoteId())
            ->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
        return $this->quoteManagement->placeOrder($quoteIdMask->getQuoteId(), $paymentMethod);
    }
}
