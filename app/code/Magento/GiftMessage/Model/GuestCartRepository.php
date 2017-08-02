<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model;

use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Shopping cart gift message repository object for guest
 * @since 2.0.0
 */
class GuestCartRepository implements GuestCartRepositoryInterface
{
    /**
     * @var CartRepository
     * @since 2.0.0
     */
    protected $repository;

    /**
     * @var QuoteIdMaskFactory
     * @since 2.0.0
     */
    protected $quoteIdMaskFactory;

    /**
     * @param CartRepository $repository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @since 2.0.0
     */
    public function __construct(
        CartRepository $repository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->repository = $repository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->repository->get($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function save($cartId, MessageInterface $giftMessage)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->repository->save($quoteIdMask->getQuoteId(), $giftMessage);
    }
}
