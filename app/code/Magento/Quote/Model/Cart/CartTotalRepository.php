<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Api\CartTotalRepositoryInterface;

/**
 * Cart totals data object.
 */
class CartTotalRepository implements CartTotalRepositoryInterface
{
    /**
     * Cart totals builder.
     *
     * @var Api\Data\TotalsDataBuilder
     */
    private $totalsBuilder;

    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * Constructs a cart totals data object.
     *
     * @param Api\Data\TotalsDataBuilder $totalsBuilder Cart totals builder.
     * @param QuoteRepository $quoteRepository Quote repository.
     */
    public function __construct(
        Api\Data\TotalsDataBuilder $totalsBuilder,
        QuoteRepository $quoteRepository
    ) {
        $this->totalsBuilder = $totalsBuilder;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return Totals Quote totals data.
     */
    public function get($cartId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        $totals = array_merge($shippingAddress->getData(), $quote->getData());
        $this->totalsBuilder->populateWithArray($totals);
        $this->totalsBuilder->setItems($quote->getAllItems());

        return $this->totalsBuilder->create();
    }
}
