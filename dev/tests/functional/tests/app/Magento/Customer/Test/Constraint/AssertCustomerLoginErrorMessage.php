<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that customer login error message is displayed.
 */
class AssertCustomerLoginErrorMessage extends AbstractConstraint
{
    /**
     * Customer login error message.
     */
    const ERROR_MESSAGE = 'You did not sign in correctly or your account is temporarily disabled.';

    /**
     * Assert that customer login error message is displayed.
     *
     * @param CustomerAccountLogin $customerLogin
     * @return void
     */
    public function processAssert(
        CustomerAccountLogin $customerLogin
    ) {
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_MESSAGE,
            $customerLogin->getMessages()->getErrorMessage(),
            'Wrong error message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer login error message is displayed.';
    }
}
