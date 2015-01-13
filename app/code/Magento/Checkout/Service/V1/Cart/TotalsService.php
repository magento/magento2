<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Cart;

use Magento\Checkout\Service\V1\Data\Cart\Totals;
use Magento\Checkout\Service\V1\Data\Cart;
use Magento\Sales\Model\Quote;
use Magento\Sales\Model\QuoteRepository;

/**
 * Cart totals service object.
 */
class TotalsService implements TotalsServiceInterface
{
    /**
     * Cart totals builder.
     *
     * @var Cart\TotalsBuilder
     */
    private $totalsBuilder;

    /**
     * Cart totals mapper.
     *
     * @var Cart\TotalsMapper
     */
    private $totalsMapper;

    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * Item totals mapper.
     *
     * @var Totals\ItemMapper;
     */
    private $itemTotalsMapper;

    /**
     * Constructs a cart totals service object.
     *
     * @param Cart\TotalsBuilder $totalsBuilder Cart totals builder.
     * @param Cart\TotalsMapper $totalsMapper Cart totals mapper.
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param Totals\ItemMapper $itemTotalsMapper Item totals mapper.
     */
    public function __construct(
        Cart\TotalsBuilder $totalsBuilder,
        Cart\TotalsMapper $totalsMapper,
        QuoteRepository $quoteRepository,
        Totals\ItemMapper $itemTotalsMapper
    ) {
        $this->totalsBuilder = $totalsBuilder;
        $this->totalsMapper = $totalsMapper;
        $this->quoteRepository = $quoteRepository;
        $this->itemTotalsMapper = $itemTotalsMapper;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return Totals Quote totals data.
     */
    public function getTotals($cartId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Sales\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);

        $this->totalsBuilder->populateWithArray($this->totalsMapper->map($quote));
        $items = [];
        foreach ($quote->getAllItems() as $item) {
            $items[] = $this->itemTotalsMapper->extractDto($item);
        }
        $this->totalsBuilder->setItems($items);

        return $this->totalsBuilder->create();
    }
}
