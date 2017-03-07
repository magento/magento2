<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert js validation error messages are present for required fields.
 */
class AssertCreditCardJsValidationMessagesArePresent extends AbstractConstraint
{
    /**
     * Assert js validation error messages are present for required fields.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $expectedErrorMessages
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, array $expectedErrorMessages)
    {
        $errorMessages = $checkoutOnepage->getBraintreeBlock()->getVisibleMessages($expectedErrorMessages);

        foreach (array_keys($errorMessages) as $field) {
            \PHPUnit_Framework_Assert::assertEquals(
                $expectedErrorMessages[$field],
                $errorMessages[$field],
                "Wrong js validation error message is displayed for field: $field."
            );
        }
    }

    /**
     * Returns string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Js validation error messages are correct for required fields.';
    }
}
