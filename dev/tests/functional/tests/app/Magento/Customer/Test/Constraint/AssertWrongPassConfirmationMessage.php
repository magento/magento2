<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountEdit;
use Mtf\Constraint\AbstractConstraint;

/**
 * Check that conformation message is present.
 */
class AssertWrongPassConfirmationMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Conformation message
     */
    const CONFIRMATION_MESSAGE = 'Please enter the same value again.';

    /**
     * Assert that conformation message is present.
     *
     * @param CustomerInjectable $customer
     * @param CustomerAccountEdit $customerAccountEdit
     * @return void
     */
    public function processAssert(CustomerInjectable $customer, CustomerAccountEdit $customerAccountEdit)
    {
        $validationMessages = $customerAccountEdit->getAccountInfoForm()->getValidationMessages($customer);
        if (isset($validationMessages['password_confirmation'])) {
            \PHPUnit_Framework_Assert::assertEquals(
                self::CONFIRMATION_MESSAGE,
                $validationMessages['password_confirmation'],
                'Wrong password confirmation validation text message.'
            );
        } else {
            \PHPUnit_Framework_TestCase::fail('Password confirmation validation message is absent.');
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Password confirmation validation text message is displayed.';
    }
}
