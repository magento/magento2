<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\MergeCarts;

use Magento\Quote\Api\Data\CartInterface;

interface CartQuantityValidatorInterface
{
    /**
     * Validate cart quantities when merging
     *
     * @param CartInterface $customerCart
     * @param CartInterface $guestCart
     * @return bool
     */
    public function validateFinalCartQuantities(CartInterface $customerCart, CartInterface $guestCart): bool;
}
