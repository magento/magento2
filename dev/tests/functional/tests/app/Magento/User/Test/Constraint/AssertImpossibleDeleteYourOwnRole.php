<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Page\Adminhtml\UserRoleEditRole;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertRoleSuccessSaveMessage
 */
class AssertImpossibleDeleteYourOwnRole extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const ERROR_MESSAGE = 'You cannot delete self-assigned roles.';

    /**
     * Asserts that error message equals to expected message.
     *
     * @param UserRoleEditRole $rolePage
     * @return void
     */
    public function processAssert(UserRoleEditRole $rolePage)
    {
        $errorMessage = $rolePage->getMessagesBlock()->getErrorMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_MESSAGE,
            $errorMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::ERROR_MESSAGE
            . "\nActual: " . $errorMessage
        );
    }

    /**
     * Returns message if equals to expected message.
     *
     * @return string
     */
    public function toString()
    {
        return '"You cannot delete self-assigned roles." message on EditRole page is correct.';
    }
}
