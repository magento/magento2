<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Catalog\Test\Fixture\Cart\Item;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

class AssertVisibleItemsQtyInMiniShoppingCart extends AbstractConstraint
{
    /**
     * Assert that quantity of visible Cart items are the same as minicart configuration value.
     *
     * @param CmsIndex $cmsIndex
     * @param Cart $cart
     * @param int $minicartMaxVisibleCartItemsCount
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, Cart $cart, $minicartMaxVisibleCartItemsCount)
    {
        $sidebar = $cmsIndex->getCartSidebarBlock();
        $sidebar->openMiniCart();

        $sourceProducts = $cart->getDataFieldConfig('items')['source'];
        $products = $sourceProducts->getProducts();

        $presentItems = 0;
        /** @var Item $item */
        foreach (array_keys($cart->getItems()) as $key) {
            /** @var CatalogProductSimple $product */
            $product = $products[$key];
            if ($sidebar->getCartItem($product)->isVisible()) {
                $presentItems++;
            }
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $minicartMaxVisibleCartItemsCount,
            $presentItems,
            'Wrong quantity of visible Cart items in mini shopping cart'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quantity of visible Cart items the same as minicart configuration value.';
    }
}
