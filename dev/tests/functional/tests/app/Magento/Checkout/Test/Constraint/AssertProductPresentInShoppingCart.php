<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductPresentInShoppingCart
 * Assert that products are present in shopping cart
 */
class AssertProductPresentInShoppingCart extends AbstractConstraint
{
    /**
     * Assert that products are present in shopping cart
     *
     * @param CheckoutCart $checkoutCart
     * @param array $products
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, array $products)
    {
        $checkoutCart->open();
        foreach ($products as $product) {
            \PHPUnit_Framework_Assert::assertTrue(
                $checkoutCart->getCartBlock()->getCartItem($product)->isVisible(),
                'Product ' . $product->getName() . ' is absent in shopping cart.'
            );
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'All expected products are present in shopping cart.';
    }
}
