<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Assert that customer login error message is displayed.
 */
class AssertCustomerLoginErrorMessage extends AbstractConstraint
{
    /**
     * Customer login error message.
     */
    const ERROR_MESSAGE = 'Invalid login or password.';

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
