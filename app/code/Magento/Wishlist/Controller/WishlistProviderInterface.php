<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller;

/**
 * Interface \Magento\Wishlist\Controller\WishlistProviderInterface
 *
 */
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
