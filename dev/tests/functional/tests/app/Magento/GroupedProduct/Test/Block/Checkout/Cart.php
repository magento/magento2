<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Checkout;

use Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Cart
 * Shopping cart block
 */
class Cart extends \Magento\Checkout\Test\Block\Cart
{
    /**
     * Get cart item block
     *
     * @param FixtureInterface $product
     * @return \Magento\Checkout\Test\Block\Cart\CartItem
     */
    public function getCartItem(FixtureInterface $product)
    {
        return $this->blockFactory->create(
            'Magento\GroupedProduct\Test\Block\Checkout\Cart\CartItem',
            [
                'element' => $this->_rootElement,
                'config' => [
                    'associated_cart_items' => $this->findCartItems($product),
                ]
            ]
        );
    }

    /**
     * Find cart item blocks for associated products
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function findCartItems(FixtureInterface $product)
    {
        $cartItems = [];

        /** @var GroupedProductInjectable $product */
        $associatedProducts = $product->getAssociated()['products'];
        foreach ($associatedProducts as $product) {
            $cartItems[$product->getSku()] = parent::getCartItem($product);
        }

        return $cartItems;
    }
}
