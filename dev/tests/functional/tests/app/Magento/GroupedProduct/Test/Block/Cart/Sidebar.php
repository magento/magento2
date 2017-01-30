<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Cart;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Checkout\Test\Block\Cart\Sidebar as MiniShoppingCart;

/**
 * Mini shopping cart block.
 */
class Sidebar extends MiniShoppingCart
{
    /**
     * Get cart item block.
     *
     * @param FixtureInterface $product
     * @return \Magento\GroupedProduct\Test\Block\Cart\Sidebar\Item
     */
    public function getCartItem(FixtureInterface $product)
    {
        return $this->blockFactory->create(
            'Magento\GroupedProduct\Test\Block\Cart\Sidebar\Item',
            [
                'element' => $this->_rootElement,
                'config' => [
                    'associated_cart_items' => $this->findCartItems($product),
                ]
            ]
        );
    }

    /**
     * Find cart item blocks for associated products.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function findCartItems(FixtureInterface $product)
    {
        $cartItems = [];

        /** @var GroupedProduct $product */
        $associatedProducts = $product->getAssociated()['products'];
        foreach ($associatedProducts as $product) {
            $cartItems[$product->getSku()] = parent::getCartItem($product);
        }

        return $cartItems;
    }
}
