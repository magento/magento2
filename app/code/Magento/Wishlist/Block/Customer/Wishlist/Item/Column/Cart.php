<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Customer\Wishlist\Item\Column;

/**
 * Wishlist block customer item cart column
 *
 * @api
 * @since 2.0.0
 */
class Cart extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
{
    /**
     * Returns qty to show visually to user
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @return float
     * @since 2.0.0
     */
    public function getAddToCartQty(\Magento\Wishlist\Model\Item $item)
    {
        $qty = $item->getQty();
        return $qty ? $qty : 1;
    }

    /**
     * Return product for current item
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProductItem()
    {
        return $this->getItem()->getProduct();
    }
}
