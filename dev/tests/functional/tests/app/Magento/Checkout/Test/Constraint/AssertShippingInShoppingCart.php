<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that shipping amount is equal to expected
 */
class AssertShippingInShoppingCart extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that shipping amount is equal to expected.
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
        }

        $fixtureShippingAmount = number_format((float)$cart->getShippingAmount(), 2);
        $pageShippingAmount = $checkoutCart->getTotalsBlock()->getShippingPrice();
        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureShippingAmount,
            $pageShippingAmount,
            'Shipping amount in the shopping cart not equals to shipping amount from fixture.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shipping amount in the shopping cart equals to expected shipping amount from data set.';
    }
}
