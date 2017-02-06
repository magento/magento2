<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\User\Test\Page\Adminhtml\UserIndex;

/**
 * Assert to check change password error appearance.
 */
class AssertUserPasswordChangedSuccessfully extends AbstractConstraint
{
    /**
     * Fail message when provided password have been in use.
     */
    const FAIL_MESSAGE = 'Sorry, but this password has already been used. Please create another.';

    /**
     * Asserts that failed message equals to expected message.
     *
     * @param UserIndex $userIndex
     * @return void
     */
    public function processAssert(UserIndex $userIndex)
    {
        $errorMessage = $userIndex->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::FAIL_MESSAGE,
            $errorMessage,
            'Password update failed with error: "' . self::FAIL_MESSAGE . '"'
        );
    }

    /**
     * Returns success message if there is fail message.
     *
     * @return string
     */
    public function toString()
    {
        return 'Password validation completed successfully.';
    }
}
