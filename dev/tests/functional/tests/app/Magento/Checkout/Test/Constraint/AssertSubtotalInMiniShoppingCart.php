<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert that subtotal in mini shopping cart equals to expected total from data set.
 */
class AssertSubtotalInMiniShoppingCart extends AbstractAssertForm
{
    /**
     * Assert that subtotal in mini shopping cart equals to expected total from data set.
     *
     * @param CmsIndex $cmsIndex
     * @param Cart $cart
     * @param boolean $requireReload [optional]
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, Cart $cart, $requireReload = true)
    {
        if ($requireReload) {
            $cmsIndex->open();
        }
        $fixtureSubtotal = number_format($cart->getSubtotal(), 2);
        $miniCartSubtotal = $cmsIndex->getCartSidebarBlock()->getSubtotal();
        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureSubtotal,
            $miniCartSubtotal,
            'Subtotal price in the shopping cart is not equal to subtotal price from fixture.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Subtotal in the shopping cart equals to expected total from data set.';
    }
}
