<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Api\Data\TotalsItemExtensionInterface;

/**
 * Test helper for TotalsItemExtension to support extension attribute methods
 */
class TotalsItemExtensionTestHelper implements TotalsItemExtensionInterface
{
    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterface|null
     */
    private $negotiableQuoteItemTotals;

    /**
     * Get negotiable quote item totals
     *
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterface|null
     */
    public function getNegotiableQuoteItemTotals()
    {
        return $this->negotiableQuoteItemTotals;
    }

    /**
     * Set negotiable quote item totals
     *
     * @param \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterface|null $totals
     * @return $this
     */
    public function setNegotiableQuoteItemTotals($totals)
    {
        $this->negotiableQuoteItemTotals = $totals;
        return $this;
    }
}
