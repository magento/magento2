<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestCartTotalRepositoryInterface;
use Magento\Quote\Model\Cart\CartTotalRepository;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Cart totals repository class for guest carts.
 */
class GuestCartTotalRepository extends CartTotalRepository implements GuestCartTotalRepositoryInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * Constructs a cart totals data object.
     *
     * @param \Magento\Quote\Api\Data\TotalsInterfaceFactory $totalsFactory Cart totals factory.
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Magento\Quote\Api\Data\TotalsInterfaceFactory $totalsFactory,
        QuoteRepository $quoteRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        parent::__construct($totalsFactory, $quoteRepository, $dataObjectHelper);
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return parent::get($quoteIdMask->getId());
    }
}
