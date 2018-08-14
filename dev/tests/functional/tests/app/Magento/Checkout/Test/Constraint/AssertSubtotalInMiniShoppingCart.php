<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert that subtotal in mini shopping cart equals to expected subtotal from data set.
 */
class AssertSubtotalInMiniShoppingCart extends AbstractAssertForm
{
    /**
     * Assert that subtotal in mini shopping cart equals to expected subtotal from data set.
     *
     * @param CmsIndex $cmsIndex
     * @param Cart $cart
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, Cart $cart)
    {
        $cmsIndex->open();
        $fixtureSubtotal = number_format($cart->getSubtotal(), 2);
        $miniCartSubtotal = $cmsIndex->getCartSidebarBlock()->getSubtotal();
        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureSubtotal,
            $miniCartSubtotal,
            'Subtotal price in mini shopping cart is not equal to subtotal price from fixture.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Subtotal in mini shopping cart equals to expected subtotal from data set.';
    }
}
