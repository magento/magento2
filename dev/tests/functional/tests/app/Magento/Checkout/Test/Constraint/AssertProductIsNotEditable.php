<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductIsNotEditable
 * Assert that after adding products by sku to shopping cart products are not editable
 */
class AssertProductIsNotEditable extends AbstractConstraint
{
    /**
     * Assert that after adding products by sku to shopping cart products are not editable
     *
     * @param CheckoutCart $checkoutCart
     * @param array $products
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, array $products)
    {
        foreach ($products as $product) {
            \PHPUnit_Framework_Assert::assertFalse(
                $checkoutCart->getCartBlock()->getCartItem($product)->isEditButtonVisible(),
                'Added product is editable.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Added products by sku to shopping cart are not editable';
    }
}
