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
 * Assert that tax amount is equal to expected
 */
class AssertTaxInShoppingCart extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that tax amount is equal to expected.
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

        $fixtureTaxAmount = number_format((float)$cart->getTaxAmount(), 2);
        $pageTaxAmount = $checkoutCart->getTotalsBlock()->getTax();
        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureTaxAmount,
            $pageTaxAmount,
            'Tax amount in the shopping cart not equals to tax amount from fixture.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax amount in the shopping cart equals to expected tax amount from data set.';
    }
}
