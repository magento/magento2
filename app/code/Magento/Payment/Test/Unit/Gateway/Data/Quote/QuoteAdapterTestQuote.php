<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Data\Quote;

use Magento\Quote\Model\Quote;

class QuoteAdapterTestQuote extends Quote
{
    public function getBaseGrandTotal()
    {
        return null;
    }
}
