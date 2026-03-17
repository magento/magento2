<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

class AddFreeGiftOnCartLoad
{
    public function __construct(
        private readonly AddFreeGiftToQuote $addFreeGiftToQuote
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(GetCartForUser $subject, Quote $result): Quote
    {
        $this->addFreeGiftToQuote->addFreeGifts($result);
        return $result;
    }
}
