<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert fields visibility in customer account information tab.
 */
class AssertDefaultAccountInformation extends AbstractConstraint
{
    /**
     * Assert fields visibility in customer account information tab.
     *
     * @param CustomerAccountEdit $customerAccountEdit
     * @return void
     */
    public function processAssert(
        CustomerAccountEdit $customerAccountEdit
    ) {
        $infoForm = $customerAccountEdit->getAccountInfoForm();

        \PHPUnit_Framework_Assert::assertFalse(
            $infoForm->isEmailVisible(),
            'Email text field should not be visible.'
        );

        \PHPUnit_Framework_Assert::assertFalse(
            $infoForm->isCurrentPasswordVisible(),
            'Current Password text field should not be visible.'
        );

        \PHPUnit_Framework_Assert::assertFalse(
            $infoForm->isPasswordVisible(),
            'New Password text field should not be visible.'
        );

        \PHPUnit_Framework_Assert::assertFalse(
            $infoForm->isConfirmPasswordVisible(),
            'Password Confirmation text field should not be visible.'
        );
    }

    /**
     * String representation of success assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Default customer account information tab is correct.';
    }
}
