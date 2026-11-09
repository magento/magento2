<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\GiftMessage\Model;

use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Api\GuestItemRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\GuestCart\GetGuestCart;

/**
 * Shopping cart gift message item repository object for guest
 */
class GuestItemRepository implements GuestItemRepositoryInterface
{
    /**
     * @var ItemRepository
     */
    protected $repository;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var GetGuestCart|null
     */
    private $getGuestCart;

    /**
     * @param ItemRepository $repository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GetGuestCart|null $getGuestCart
     */
    public function __construct(
        ItemRepository $repository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        ?GetGuestCart $getGuestCart = null
    ) {
        $this->repository = $repository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->getGuestCart = $getGuestCart ?? ObjectManager::getInstance()->get(GetGuestCart::class);
    }

    /**
     * @inheritDoc
     */
    public function get($cartId, $itemId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        $quoteItem = $this->repository->get($quoteIdMask->getQuoteId(), $itemId);
        return $quoteItem;
    }

    /**
     * @inheritDoc
     */
    public function save($cartId, MessageInterface $giftMessage, $itemId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->repository->save($quoteIdMask->getQuoteId(), $giftMessage, $itemId);
    }
}
