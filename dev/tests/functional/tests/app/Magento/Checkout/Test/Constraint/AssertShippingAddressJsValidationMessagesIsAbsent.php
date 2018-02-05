<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertShippingAddressJsValidationMessagesIsAbsent
 * Assert js validation messages are absent for required fields.
 */
class AssertShippingAddressJsValidationMessagesIsAbsent extends AbstractConstraint
{
    /**
     * Assert js validation messages are absent for required fields.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage)
    {
        $requiredFields = $checkoutOnepage->getShippingBlock()->getRequiredFields();

        /** @var \Magento\Mtf\Client\ElementInterface $field */
        foreach ($requiredFields as $field) {
            $errorContainer = $field->find("div .field-error");
            \PHPUnit_Framework_Assert::assertFalse(
                $errorContainer->isVisible(),
                'Js validation error messages must be absent for required fields after checkout start.'
            );
        }
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Js validation messages are absent for required fields.';
    }
}
