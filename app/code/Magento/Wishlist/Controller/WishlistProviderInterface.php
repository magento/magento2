<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Wishlist\Controller;

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
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getWishlist($wishlistId = null);
}
