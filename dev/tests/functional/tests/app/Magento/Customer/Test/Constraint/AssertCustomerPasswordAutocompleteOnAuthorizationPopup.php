<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that autocomplete on password field on authorization pop up is off.
 */
class AssertCustomerPasswordAutocompleteOnAuthorizationPopup extends AbstractConstraint
{
    /**
     * Assert that autocomplete on password field on authorization pop up is off.
     *
     * @param CheckoutOnepage $checkoutPage
     * @param CheckoutCart $cartPage
     * @return void
     */
    public function processAssert(
        CheckoutOnepage $checkoutPage,
        CheckoutCart $cartPage
    ) {
        $cartPage->open();
        $cartPage->getProceedToCheckoutBlock()->proceedToCheckout();

        \PHPUnit_Framework_Assert::assertTrue(
            $checkoutPage->getAuthenticationPopupBlock()->isPasswordAutocompleteOff(),
            'Password field autocomplete is not off.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that autocomplete is off.';
    }
}
