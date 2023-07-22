<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller;

use Magento\Wishlist\Model\Wishlist;

/**
 * Interface \Magento\Wishlist\Controller\WishlistProviderInterface
 *
 * @api
 */
interface WishlistProviderInterface
{
    /**
     * Retrieve wishlist
     *
     * @param string $wishlistId
     * @return Wishlist
     */
    public function getWishlist($wishlistId = null);
}
