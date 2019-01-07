<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that error message is correct.
 */
class AssertCheckoutErrorMessage extends AbstractConstraint
{
    /**
     * Assert that error message is correct.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $expectedErrorMessage
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $expectedErrorMessage)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedErrorMessage,
            $checkoutOnepage->getMessagesBlock()->getErrorMessage(),
            'Wrong error message is displayed.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Error message on Checkout onepage page is correct.';
    }
}
