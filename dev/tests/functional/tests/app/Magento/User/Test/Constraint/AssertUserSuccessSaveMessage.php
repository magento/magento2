<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Page\Adminhtml\UserIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserSuccessSaveMessage
 */
class AssertUserSuccessSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_MESSAGE = 'You saved the user.';

    /**
     * Asserts that success message equals to expected message.
     *
     * @param UserIndex $userIndex
     * @return void
     */
    public function processAssert(UserIndex $userIndex)
    {
        $successMessage = $userIndex->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $successMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $successMessage
        );
    }

    /**
     * Returns success message if equals to expected message.
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message on User Index page is correct.';
    }
}
