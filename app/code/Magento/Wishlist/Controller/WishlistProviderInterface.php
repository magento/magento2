<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller;

interface WishlistProviderInterface
{
    /**
     * Retrieve wishlist
     *
     * @param string $wishlistId
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getWishlist($wishlistId = null);
}
