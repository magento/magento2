<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Assert that customer forgot password message is present on customer account forgot password page.
 */
class AssertCustomerForgotPasswordSuccessMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE =
        'If there is an account associated with %s you will receive an email with a link to reset your password.';

    /**
     * Assert that customer forgot password message is present on customer account forgot password page.
     *
     * @param CustomerAccountLogin $customerLogin
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CustomerAccountLogin $customerLogin,
        Customer $customer
    ) {
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_MESSAGE, $customer->getEmail()),
            $customerLogin->getMessages()->getSuccessMessage(),
            'Wrong forgot password message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer forgot password message is present on customer account forgot password page.';
    }
}
