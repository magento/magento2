<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\GuestCartTotalRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\GuestCart\GetGuestCart;
use Magento\Framework\App\ObjectManager;

/**
 * Cart totals repository class for guest carts.
 */
class GuestCartTotalRepository implements GuestCartTotalRepositoryInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalRepository;

    /**
     * @var GetGuestCart|null
     */
    private $getGuestCart;

    /**
     * Constructs a cart totals data object.
     *
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GetGuestCart|null $getGuestCart
     */
    public function __construct(
        CartTotalRepositoryInterface $cartTotalRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        ?GetGuestCart $getGuestCart = null
    ) {
        $this->cartTotalRepository = $cartTotalRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->getGuestCart = $getGuestCart ?? ObjectManager::getInstance()->get(GetGuestCart::class);
    }

    /**
     * @inheritDoc
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->cartTotalRepository->get($quoteIdMask->getQuoteId());
    }
}
