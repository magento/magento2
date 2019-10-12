<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that success message appears after click on 'Force Sing-In' button.
 */
class AssertAccessTokensSuccessfullyRevoked extends AbstractConstraint
{
    /**
     * User revoke tokens success message.
     */
    const SUCCESS_MESSAGE = 'You have revoked the user\'s tokens.';

    /**
     * Assert that success message appears after click on 'Force Sing-In' button.
     *
     * @param UserEdit $userEdit
     * @return void
     */
    public function processAssert(UserEdit $userEdit): void
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $userEdit->getMessagesBlock()->getSuccessMessage()
        );
    }

    /**
     * Return string representation of object
     *
     * @return string
     */
    public function toString()
    {
        return self::SUCCESS_MESSAGE . ' message is present on UserEdit page.';
    }
}
