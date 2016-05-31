<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertIncorrectUserPassword
 */
class AssertIncorrectUserPassword extends AbstractConstraint
{
    const ERROR_MESSAGE = 'You have entered an invalid password for current user.';

    /**
     * Asserts that invalid password message equals to expected message.
     *
     * @param UserRoleIndex $rolePage
     * @return void
     */
    public function processAssert(UserRoleIndex $rolePage)
    {
        $errorMessage = $rolePage->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_MESSAGE,
            $errorMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::ERROR_MESSAGE
            . "\nActual: " . $errorMessage
        );
    }

    /**
     * Returns success message if equals to expected message.
     *
     * @return string
     */
    public function toString()
    {
        return 'Incorrect password message is present and correct.';
    }
}
