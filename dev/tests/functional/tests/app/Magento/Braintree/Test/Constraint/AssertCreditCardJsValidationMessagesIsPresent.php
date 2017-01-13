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
class AssertCreditCardJsValidationMessagesIsPresent extends AbstractConstraint
{
    /**
     * Assert js validation message is present for required field.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $expectedErrorMessage
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $expectedErrorMessage)
    {
        $requiredFields = $checkoutOnepage->getBraintreeBlock()->getRequiredFields();

        /** @var \Magento\Mtf\Client\ElementInterface $field */
        foreach ($requiredFields as $field) {
            $errorContainer = $field->find(".hosted-error");
            if ($errorContainer->isVisible()) {
                \PHPUnit_Framework_Assert::assertEquals(
                    $expectedErrorMessage,
                    $errorContainer->getText(),
                    'Wrong js validation error message is displayed.'
                );
            }
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
