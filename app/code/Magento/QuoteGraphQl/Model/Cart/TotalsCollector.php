<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\TotalsCollector as QuoteTotalsCollector;

/**
 * Helper class to eliminate redundant expensive total calculations
 */
class TotalsCollector
{
    /**
     * @var QuoteTotalsCollector
     */
    private $quoteTotalsCollector;

    /**
     * @var Total[]
     */
    private $quoteTotals;

    /**
     * @var Total[][]
     */
    private $addressTotals;

    /**
     * @param QuoteTotalsCollector $quoteTotalsCollector
     */
    public function __construct(QuoteTotalsCollector $quoteTotalsCollector)
    {
        $this->quoteTotalsCollector = $quoteTotalsCollector;
        $this->quoteTotals = [];
        $this->addressTotals = [];
    }

    /**
     * Clear stored totals to force them to be recalculated the next time they're requested
     *
     * This is relevant for mutations which can change the results
     */
    public function clearTotals(): void
    {
        $this->quoteTotals = [];
        $this->addressTotals = [];
    }

    /**
     * Calls the base collectQuoteTotals() only if it hasn't been called before for this quote
     *
     * If the totals could have changed since the last invocation, $forceRecalculate should be true
     *
     * @param Quote $quote
     * @param bool $forceRecalculate
     * @return Total
     * @see QuoteTotalsCollector::collectQuoteTotals()
     */
    public function collectQuoteTotals(Quote $quote, bool $forceRecalculate = false): Total
    {
        if ($quote->getId() === null) {
            return $this->quoteTotalsCollector->collectQuoteTotals($quote);
        }
        $quoteId = (string)$quote->getId();
        if (!isset($this->quoteTotals[$quoteId]) || $forceRecalculate) {
            $this->quoteTotals[$quoteId] = $this->quoteTotalsCollector->collectQuoteTotals($quote);
        }
        return $this->quoteTotals[$quoteId];
    }

    /**
     * Calls the base collectAddressTotals() only if it hasn't been called before for this address
     *
     * If the totals could have changed since the last invocation, $forceRecalculate should be true
     *
     * @param Quote $quote
     * @param Address $address
     * @param bool $forceRecalculate
     * @return Total
     * @see QuoteTotalsCollector::collectAddressTotals()
     */
    public function collectAddressTotals(Quote $quote, Address $address, bool $forceRecalculate = false): Total
    {
        if ($quote->getId() === null || $address->getId() === null) {
            return $this->quoteTotalsCollector->collectAddressTotals($quote, $address);
        }
        $quoteId = (string)$quote->getId();
        $addressId = (string)$address->getId();
        if (!isset($this->addressTotals[$quoteId])) {
            $this->addressTotals[$quoteId] = [];
        }
        if (!isset($this->addressTotals[$quoteId][$addressId]) || $forceRecalculate) {
            $this->addressTotals[$quoteId][$addressId] =
                $this->quoteTotalsCollector->collectAddressTotals($quote, $address);
        }

        return $this->addressTotals[$quoteId][$addressId];
    }
}
