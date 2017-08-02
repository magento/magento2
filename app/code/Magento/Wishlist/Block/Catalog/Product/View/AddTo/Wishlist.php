<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Catalog\Product\View\AddTo;

/**
 * Product view wishlist block
 *
 * @api
 * @since 2.2.0
 */
class Wishlist extends \Magento\Catalog\Block\Product\View
{
    /**
     * Return wishlist widget options json
     *
     * @return string
     * @since 2.2.0
     */
    public function getWishlistOptionsJson()
    {
        return $this->_jsonEncoder->encode($this->getWishlistOptions());
    }

    /**
     * Return wishlist widget options
     *
     * @return array
     * @since 2.2.0
     */
    public function getWishlistOptions()
    {
        return ['productType' => $this->escapeHtml($this->getProduct()->getTypeId())];
    }

    /**
     * Return wishlist params
     *
     * @return string
     * @since 2.2.0
     */
    public function getWishlistParams()
    {
        $product = $this->getProduct();
        return $this->_wishlistHelper->getAddParams($product);
    }

    /**
     * Check whether the wishlist is allowed
     *
     * @return string
     * @since 2.2.0
     */
    public function isWishListAllowed()
    {
        return $this->_wishlistHelper->isAllow();
    }
}
