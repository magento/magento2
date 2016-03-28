<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertPasswordIsNotSecureEnoughMessage
 */
class AssertPasswordIsNotSecureEnoughMessage extends AbstractConstraint
{
    /**
     * Assert that appropriate message is displayed on "Create New Customer Account" page(frontend) if password is not
     * secure enough.
     *
     * @param CustomerAccountCreate $registerPage
     * @return void
     */
    public function processAssert(CustomerAccountCreate $registerPage)
    {
        $expectedErrorMessage = 'Minimum of different classes of characters in password is 3.' .
            ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.';
        $errorMessage = $registerPage->getRegisterForm()->getPasswordError();
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedErrorMessage,
            $errorMessage,
            'The messages are not equal.'
        );
    }

    /**
     * Assert that displayed error message is correct
     *
     * @return string
     */
    public function toString()
    {
        return 'Password insecure message is present on customer registration page.';
    }
}
