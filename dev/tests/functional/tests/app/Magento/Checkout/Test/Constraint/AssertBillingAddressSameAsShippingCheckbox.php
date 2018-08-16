<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that "Same as Shipping" checkbox of Billing Address selection block is in correct state.
 */
class AssertBillingAddressSameAsShippingCheckbox extends AbstractConstraint
{
    /**
     * Assert that "Same as Shipping" checkbox of Billing Address selection block is in correct state.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $billingCheckboxState
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $billingCheckboxState)
    {
        $actualResult = $checkoutOnepage
            ->getPaymentBlock()
            ->getSelectedPaymentMethodBlock()
            ->getBillingBlock()
            ->getSameAsShippingCheckboxValue();

        \PHPUnit_Framework_Assert::assertEquals(
            $billingCheckboxState,
            $actualResult,
            '"Same as Shipping" checkbox has wrong value'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return '"Same as Shipping" checkbox has correct value.';
    }
}
