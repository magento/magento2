<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Api;

interface AddItemToWishlistInterface
{
    public function execute(
        \Magento\Wishlist\Api\Data\WishlistInterface $wishlist,
        \Magento\Wishlist\Api\Data\ItemInterface $item
    ): \Magento\Wishlist\Api\Data\ItemInterface;
}
