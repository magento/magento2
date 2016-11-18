<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that error message is correct.
 */
class AssertOrderFailedMessage extends AbstractConstraint
{
    /**
     * Error message on checkout.
     */
    const ERROR_MESSAGE = 'Transaction has been declined. Please try again later.';

    /**
     * Assert that error message is correct.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_MESSAGE,
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
