<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that success message is present and has correct text.
 */
class AssertSuccessMessageInShoppingCart extends AbstractConstraint
{
    /**
     * Assert that success message is present and has correct text.
     *
     * @param CheckoutCart $checkoutCart
     * @param string $expectedMessage
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, $expectedMessage)
    {
        $actualMessage = $checkoutCart->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedMessage,
            $actualMessage,
            'Success message is not present or has wrong text.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message is present or has a correct text.';
    }
}
