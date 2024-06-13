<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Is cart active (can it be retrieved and updated). Requires for extensions that require to work with inactive cart.
 */
class IsActive
{
    /**
     * Is cart active
     *
     * @param CartInterface $cart
     * @return bool
     */
    public function execute(CartInterface $cart): bool
    {
        return (bool) $cart->getIsActive();
    }
}
