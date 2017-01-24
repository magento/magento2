<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;

class AssertItemsCounterInMiniShoppingCart extends AbstractConstraint
{
    /**
     * Assert that products qty in cart is equal to fixtures count.
     *
     * @param CmsIndex $cmsIndex
     * @param int $totalItemsCountInShoppingCart
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, $totalItemsCountInShoppingCart)
    {
        $sidebar = $cmsIndex->getCartSidebarBlock();
        $sidebar->openMiniCart();

        \PHPUnit_Framework_Assert::assertEquals(
            $sidebar->getItemsCounter(),
            $totalItemsCountInShoppingCart,
            'Wrong quantity of Cart items in mini shopping cart'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products qty in cart is equal to fixtures count';
    }
}
