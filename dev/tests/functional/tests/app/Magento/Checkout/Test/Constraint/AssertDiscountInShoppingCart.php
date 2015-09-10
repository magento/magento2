<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertDiscountInShoppingCart
 *
 * Assert that discount is equal to expected.
 */
class AssertDiscountInShoppingCart extends AbstractConstraint
{
    /**
     * Assert that discount is equal to expected.
     *
     * @param CheckoutCart $checkoutCart
     * @param Cart $cart
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Cart $cart)
    {
        $checkoutCart->open();
        $checkoutCart->getTotalsBlock()->waitForUpdatedTotals();
        \PHPUnit_Framework_Assert::assertEquals(
            number_format($cart->getDiscount(), 2),
            $checkoutCart->getTotalsBlock()->getDiscount(),
            'Discount amount in the shopping cart not equals to discount amount from fixture.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Discount in the shopping cart equals to expected discount amount from data set.';
    }
}
