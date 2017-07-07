<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that email validation message is correct.
 */
class AssertEmailErrorValidationMessage extends AbstractConstraint
{
    /**
     * Email validation message.
     */
    const EMAIL_VALIDATION_MESSAGE = 'Please enter a valid email address (Ex: johndoe@domain.com).';

    /**
     * Assert that email validation message is correct.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @return void
     */
    public function processAssert(
        CheckoutOnepage $checkoutOnepage
    ) {
        \PHPUnit_Framework_Assert::assertEquals(
            self::EMAIL_VALIDATION_MESSAGE,
            $checkoutOnepage->getShippingBlock()->getEmailError(),
            'Email validation message is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Email validation message is correct.';
    }
}
