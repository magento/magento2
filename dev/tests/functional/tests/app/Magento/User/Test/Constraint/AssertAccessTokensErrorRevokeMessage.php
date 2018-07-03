<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAccessTokensErrorRevokeMessage
 * Assert that error message appears after click on 'Force Sing-In' button for user without tokens.
 */
class AssertAccessTokensErrorRevokeMessage extends AbstractConstraint
{
    /**
     * User revoke tokens error message.
     */
    const ERROR_MESSAGE = 'This user has no tokens.';

    /**
     * Assert that error message appears after click on 'Force Sing-In' button for user without tokens.
     *
     * @param UserEdit $userEdit
     * @return void
     */
    public function processAssert(UserEdit $userEdit)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::ERROR_MESSAGE,
            $userEdit->getMessagesBlock()->getErrorMessage()
        );
    }

    /**
     * Return string representation of object
     *
     * @return string
     */
    public function toString()
    {
        return self::ERROR_MESSAGE . ' error message is present on UserEdit page.';
    }
}
