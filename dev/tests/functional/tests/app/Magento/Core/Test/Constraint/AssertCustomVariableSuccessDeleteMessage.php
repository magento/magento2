<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Test\Constraint;

use Magento\Core\Test\Page\Adminhtml\SystemVariableIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomVariableSuccessDeleteMessage
 */
class AssertCustomVariableSuccessDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_DELETE_MESSAGE = 'You deleted the custom variable.';

    /**
     * Assert that success delete message is correct after Custom System Variable deleted
     *
     * @param SystemVariableIndex $systemVariableIndexPage
     * @return void
     */
    public function processAssert(SystemVariableIndex $systemVariableIndexPage)
    {
        $actualMessage = $systemVariableIndexPage->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Custom System Variable success delete message is correct.';
    }
}
