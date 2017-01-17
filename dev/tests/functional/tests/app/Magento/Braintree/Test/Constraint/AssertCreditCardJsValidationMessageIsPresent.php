<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert js validation message is present for required field.
 */
class AssertCreditCardJsValidationMessageIsPresent extends AbstractConstraint
{
    /**
     * Assert js validation message is present for required field.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $expectedErrorMessages
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $expectedErrorMessages)
    {
        $errorMessages = $checkoutOnepage->getBraintreeBlock()->getVisibleMessages($expectedErrorMessages);

        foreach ($errorMessages as $field => $message) {
            \PHPUnit_Framework_Assert::assertEquals(
                $expectedErrorMessages[$field],
                $errorMessages[$field],
                'Wrong js validation error message is displayed.'
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
        return 'Js validation error message is correct for required field.';
    }
}
