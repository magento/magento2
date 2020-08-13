<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\BuyRequest;

use Magento\Quote\Model\Cart\Data\CartItem;

/**
 * Provides data for buy request for different types of products
 */
interface BuyRequestDataProviderInterface
{
    /**
     * Provide buy request data from add to cart item request
     *
     * @param CartItem $cartItem
     * @return array
     */
    public function execute(CartItem $cartItem): array;
}
