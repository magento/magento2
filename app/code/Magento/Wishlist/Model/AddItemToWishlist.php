<?php

namespace Magento\Wishlist\Model;

use Magento\Wishlist\Api\AddItemToWishlistInterface;

class AddItemToWishlist implements AddItemToWishlistInterface
{

    public function execute(
        \Magento\Wishlist\Api\Data\WishlistInterface $wishlist,
        \Magento\Wishlist\Api\Data\ItemInterface $item
    ): \Magento\Wishlist\Api\Data\ItemInterface {
        $item->setWishlistId($wishlist->getId());
        return $item;
    }
}
