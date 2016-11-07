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
    const EXPECTED_MAX_CHARACTERS = 'Minimum of different classes of characters in password is %s.';
    const EXPECTED_MESSAGE = ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.';

    /**
     * Assert error message is displayed after customer enter password.
     *
     * @param CustomerAccountCreate $registerPage
     * @param ConfigData $config
     * @return void
     */
    public function processAssert(CustomerAccountCreate $registerPage, ConfigData $config)
    {
        $errorMessage = $registerPage->getRegisterForm()->getPasswordError();
        $characterClassesNumber = $config
            ->getData('section')['customer/password/required_character_classes_number']['value'];

        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::EXPECTED_MAX_CHARACTERS, $characterClassesNumber) . self::EXPECTED_MESSAGE,
            $errorMessage,
            'Wrong expected message is displayed.'
            . "\nExpected: " . sprintf(self::EXPECTED_MAX_CHARACTERS, $characterClassesNumber) . self::EXPECTED_MESSAGE
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
