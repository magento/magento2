<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Data\Quote;

use Magento\Quote\Model\Quote;

/**
 * Quote double declaring the magic getter mocked by QuoteAdapterTest.
 */
class QuoteAdapterTestQuote extends Quote
{
    /**
     * Return the base grand total; overridden in test mocks.
     *
     * @return float|null
     */
    public function getBaseGrandTotal()
    {
        return null;
    }
}
