<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerIsLocked
 */
class AssertCustomerIsLocked extends AbstractConstraint
{
    const CUSTOMER_LOCKED_MESSAGE =
        'You did not sign in correctly or your account is temporarily disabled.';

    /**
     * Assert that customer locked message is present on customer login page.
     *
     * @param CustomerAccountLogin $customerLogin
     * @return void
     */
    public function processAssert(
        CustomerAccountLogin $customerLogin
    ) {
        \PHPUnit_Framework_Assert::assertEquals(
            self::CUSTOMER_LOCKED_MESSAGE,
            $customerLogin->getMessages()->getErrorMessage(),
            'Wrong message is displayed.'
        );
    }

    /**
     * Assert that displayed error message is correct
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer locked message is present on customer account login page.';
    }
}
