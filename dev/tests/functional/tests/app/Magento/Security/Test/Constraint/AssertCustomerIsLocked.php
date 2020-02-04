<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Class AssertCustomerIsLocked
 */
class AssertCustomerIsLocked extends AbstractConstraint
{
    const CUSTOMER_LOCKED_MESSAGE =
        'The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.';

    /**
     * Assert that customer locked message is present on customer login page.
     *
     * @param CustomerAccountLogin $customerLogin
     * @return void
     */
    public function processAssert(
        CustomerAccountLogin $customerLogin
    ) {
        \PHPUnit\Framework\Assert::assertEquals(
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
