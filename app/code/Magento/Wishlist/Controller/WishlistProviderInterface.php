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
 * @since 2.0.0
 */
interface WishlistProviderInterface
{
    /**
     * Retrieve wishlist
     *
     * @param string $wishlistId
     * @return \Magento\Wishlist\Model\Wishlist
     * @since 2.0.0
     */
    public function getWishlist($wishlistId = null);
}
