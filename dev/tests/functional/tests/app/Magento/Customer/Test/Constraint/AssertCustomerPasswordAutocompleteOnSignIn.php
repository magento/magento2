<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that autocomplete on password field on sign in page is off.
 */
class AssertCustomerPasswordAutocompleteOnSignIn extends AbstractConstraint
{
    /**
     * Assert that autocomplete on password field on sign in page is off.
     *
     * @param CustomerAccountLogin $loginPage
     * @return void
     */
    public function processAssert(CustomerAccountLogin $loginPage)
    {
        $loginPage->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $loginPage->getLoginBlock()->isPasswordAutocompleteOff(),
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
