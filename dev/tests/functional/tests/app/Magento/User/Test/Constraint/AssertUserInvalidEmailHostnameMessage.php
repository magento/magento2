<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserInvalidEmailHostnameMessage
 */
class AssertUserInvalidEmailHostnameMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const ERROR_MESSAGE = '\'%s\' is not a valid hostname for email address \'%s\'';

    /**
     * Asserts that error message equals to expected message.
     *
     * @param UserEdit $userEdit
     * @param User $user
     * @return void
     */
    public function processAssert(UserEdit $userEdit, User $user)
    {
        $email = $user->getEmail();
        $hostname = substr($email, strpos($email, '@')+1);
        $expectedMessage = sprintf(self::ERROR_MESSAGE, $hostname, $email);
        $actualMessage = $userEdit->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedMessage,
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . $expectedMessage
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns message if equals to expected message.
     *
     * @return string
     */
    public function toString()
    {
        return 'Error message about invalid hostname for email on creation user page is correct.';
    }
}
