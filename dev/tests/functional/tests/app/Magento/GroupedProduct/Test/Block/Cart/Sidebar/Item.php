<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Cart\Sidebar;

use Magento\Checkout\Test\Block\Cart\Sidebar\Item as ProductItem;

/**
 * Grouped Product item block on mini Cart.
 */
class Item extends ProductItem
{
    /**
     * Remove grouped product item from mini cart.
     *
     * @return void
     */
    public function removeItemFromMiniCart()
    {
        foreach ($this->config['associated_cart_items'] as $productItem) {
            /** @var ProductItem $productItem */
            $productItem->removeItemFromMiniCart();
        }
    }
}
