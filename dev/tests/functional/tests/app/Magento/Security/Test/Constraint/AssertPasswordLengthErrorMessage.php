<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertPasswordLengthErrorMessage
 */
class AssertPasswordLengthErrorMessage extends AbstractConstraint
{
    const PASSWORD_LENGTH_ERROR_MESSAGE = 'Minimum length of this field must be equal or greater than 8 symbols';

    /**
     * Assert that appropriate message is displayed on "Create New Customer Account" page(frontend) if password length
     * is below 8 characters.
     *
     * @param CustomerAccountCreate $registerPage
     * @return void
     */
    public function processAssert(CustomerAccountCreate $registerPage)
    {
        $errorMessage = $registerPage->getRegisterForm()->getPasswordError();
        \PHPUnit_Framework_Assert::assertContains(
            self::PASSWORD_LENGTH_ERROR_MESSAGE,
            $errorMessage,
            'Incorrect password error message.'
        );
    }

    /**
     * Assert that displayed error message is correct
     *
     * @return string
     */
    public function toString()
    {
        return 'Password too short message is present on customer registration page.';
    }
}
