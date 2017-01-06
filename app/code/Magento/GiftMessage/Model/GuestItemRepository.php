<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model;

use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Api\GuestItemRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

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
     * @param ItemRepository $repository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        ItemRepository $repository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->repository = $repository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId, $itemId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->repository->get($quoteIdMask->getQuoteId(), $itemId);
    }

    /**
     * {@inheritDoc}
     */
    public function save($cartId, MessageInterface $giftMessage, $itemId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->repository->save($quoteIdMask->getQuoteId(), $giftMessage, $itemId);
    }
}
