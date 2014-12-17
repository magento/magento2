<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Page\Adminhtml\UserEdit;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAccessTokensErrorRevokeMessage
 * Assert that error message appears after click on 'Force Sing-In' button for user without tokens.
 */
class AssertAccessTokensErrorRevokeMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

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
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_MESSAGE,
            $userEdit->getMessagesBlock()->getErrorMessages()
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
