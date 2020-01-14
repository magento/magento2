<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that message about incorrect user password is displayed.
 */
class AssertIncorrectUserPassword extends AbstractConstraint
{
    const ERROR_MESSAGE = 'The password entered for the current user is invalid. Verify the password and try again.';

    /**
     * Asserts that invalid password message equals to expected message.
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(Dashboard $dashboard)
    {
        $errorMessage = $dashboard->getMessagesBlock()->getErrorMessage();
        \PHPUnit\Framework\Assert::assertEquals(
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
