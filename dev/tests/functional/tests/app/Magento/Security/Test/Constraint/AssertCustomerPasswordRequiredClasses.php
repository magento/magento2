<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert error message is displayed after customer enter password.
 */
class AssertCustomerPasswordRequiredClasses extends AbstractConstraint
{
    const EXPECTED_MESSAGE = 'Minimum of different classes of characters in password is %s.' .
    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.';

    /**
     * Assert error message is displayed after customer enter password.
     *
     * @param CustomerAccountCreate $registerPage
     * @return void
     */
    public function processAssert(CustomerAccountCreate $registerPage, $characterClassesNumber)
    {
        $errorMessage = $registerPage->getRegisterForm()->getPasswordError();
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::EXPECTED_MESSAGE, $characterClassesNumber),
            $errorMessage,
            'Wrong expected message is displayed.'
            . "\nExpected: " . sprintf(self::EXPECTED_MESSAGE, $characterClassesNumber)
            . "\nActual: " . $errorMessage
        );
    }

    /**
     * Text of success register message is displayed.
     *
     * @return string
     */
    public function toString()
    {
        return "Customer's password is not correct.";
    }
}
