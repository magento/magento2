<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;

/**
 * Assert that cart item options for product(s) not display with old options.
 */
class AssertProductOptionsAbsentInShoppingCart extends AssertCartItemsOptions
{
    /**
     * Notice message.
     *
     * @var string
     */
    protected $notice = "\nProduct options from shopping cart are equals to passed from fixture:\n";

    /**
     * Error message for verify options
     *
     * @var string
     */
    protected $errorMessage = '- %s: "%s" equals of "%s"';

    /**
     * Assert that cart item options for product(s) not display with old options.
     *
     * @param CheckoutCart $checkoutCart
     * @param Cart $deletedCart
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Cart $deletedCart)
    {
        parent::processAssert($checkoutCart, $deletedCart);
    }

    /**
     * Check that params are equals.
     *
     * @param mixed $expected
     * @param mixed $actual
     * @return bool
     */
    protected function equals($expected, $actual)
    {
        return (false !== strpos($expected, $actual));
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product with options are absent in shopping cart.';
    }
}
