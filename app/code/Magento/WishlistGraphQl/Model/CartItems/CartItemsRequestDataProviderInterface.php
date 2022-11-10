<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\WishlistGraphQl\Model\CartItems;

use Magento\Wishlist\Model\Item;

/**
 * Build cart item request for adding products to cart
 */
interface CartItemsRequestDataProviderInterface
{
    /**
     * Provide cart item request from buy request to add wishlist items to cart
     *
     * @param Item $wishlistItem
     * @param string $sku
     *
     * @return array
     */
    public function execute(Item $wishlistItem, ?string $sku): array;
}
