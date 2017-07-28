<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Cart Item repository class for guest carts.
 * @since 2.0.0
 */
class GuestCartItemRepository implements \Magento\Quote\Api\GuestCartItemRepositoryInterface
{
    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     * @since 2.0.0
     */
    protected $repository;

    /**
     * @var QuoteIdMaskFactory
     * @since 2.0.0
     */
    protected $quoteIdMaskFactory;

    /**
     * Constructs a read service object.
     *
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $repository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Quote\Api\CartItemRepositoryInterface $repository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getList($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $cartItemList = $this->repository->getList($quoteIdMask->getQuoteId());
        /** @var $item CartItemInterface */
        foreach ($cartItemList as $item) {
            $item->setQuoteId($quoteIdMask->getMaskedId());
        }
        return $cartItemList;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(\Magento\Quote\Api\Data\CartItemInterface $cartItem)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartItem->getQuoteId(), 'masked_id');
        $cartItem->setQuoteId($quoteIdMask->getQuoteId());
        return $this->repository->save($cartItem);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function deleteById($cartId, $itemId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->repository->deleteById($quoteIdMask->getQuoteId(), $itemId);
    }
}
