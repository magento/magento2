<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertGrandTotalInShoppingCart
 * Assert that grand total is equal to expected
 */
class AssertGrandTotalInShoppingCart extends AbstractConstraint
{
    /**
     * Assert that grand total is equal to expected
     *
     * @param CheckoutCart $checkoutCart
     * @param Cart $cart
     * @param boolean $requireReload
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Cart $cart, $requireReload = true)
    {
        if ($requireReload) {
            $checkoutCart->open();
            $checkoutCart->getTotalsBlock()->waitForUpdatedTotals();
        }

        $fixtureGrandTotal = number_format($cart->getGrandTotal(), 2);
        $pageGrandTotal = $checkoutCart->getTotalsBlock()->getGrandTotal();
        \PHPUnit\Framework\Assert::assertEquals(
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
