<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo;

/**
 * Add product to wishlist
 *
 * @api
 * @since 100.1.1
 */
class Wishlist extends \Magento\Catalog\Block\Product\ProductList\Item\Block
{
    /**
     * @return \Magento\Wishlist\Helper\Data
     * @since 100.1.1
     */
    public function getWishlistHelper()
    {
        return $this->_wishlistHelper;
    }
}
