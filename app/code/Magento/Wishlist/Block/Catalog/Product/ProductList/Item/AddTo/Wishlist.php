<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo;

/**
 * Add product to wishlist
 */
class Wishlist extends \Magento\Catalog\Block\Product\ProductList\Item\Block
{
    /**
     * @return \Magento\Wishlist\Helper\Data
     */
    public function getWishlistHelper()
    {
        return $this->_wishlistHelper;
    }
}
