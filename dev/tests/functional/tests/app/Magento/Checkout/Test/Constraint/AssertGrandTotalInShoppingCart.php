<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertGrandTotalInShoppingCart
 * Assert that grand total is equal to expected
 */
class AssertGrandTotalInShoppingCart extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that grand total is equal to expected
     *
     * @param CheckoutCart $checkoutCart
     * @param Cart $cart
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Cart $cart)
    {
        $checkoutCart->open();

        $fixtureGrandTotal = number_format($cart->getGrandTotal(), 2);
        $pageGrandTotal = $checkoutCart->getTotalsBlock()->getGrandTotal();
        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureGrandTotal,
            $pageGrandTotal,
            'Grand total price in the shopping cart not equals to grand total price from fixture.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Grand total price in the shopping cart equals to expected grand total price from data set.';
    }
}
