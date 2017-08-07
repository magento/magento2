<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo;

/**
 * Add product to wishlist
 *
 * @api
 * @since 2.1.1
 */
class Wishlist extends \Magento\Catalog\Block\Product\ProductList\Item\Block
{
    /**
     * @return \Magento\Wishlist\Helper\Data
     * @since 2.1.1
     */
    public function getWishlistHelper()
    {
        return $this->_wishlistHelper;
    }
}
