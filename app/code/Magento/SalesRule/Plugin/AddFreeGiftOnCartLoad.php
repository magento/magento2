<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class AddFreeGiftOnCartLoad
{
    public function __construct(
        private readonly AddFreeGiftToQuote $addFreeGiftToQuote
    ) {
    }

    /**
     * After the cart is saved by any GraphQL mutation, check whether free-gift
     * rules apply and, if so, add the gift product and re-save.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CartRepositoryInterface $subject,
        mixed $result,
        CartInterface $quote
    ): mixed {
        $this->addFreeGiftToQuote->addFreeGifts($quote);
        return $result;
    }
}
