<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Constraint;

use Magento\Variable\Test\Page\Adminhtml\SystemVariableIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check success delete message is correct after Custom System Variable deleted.
 */
class AssertCustomVariableSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_SAVE_MESSAGE = 'You saved the custom variable.';

    /**
     * Assert that success delete message is correct after Custom System Variable deleted.
     *
     * @param SystemVariableIndex $systemVariableIndexPage
     * @return void
     */
    public function processAssert(SystemVariableIndex $systemVariableIndexPage)
    {
        $actualMessage = $systemVariableIndexPage->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_SAVE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_SAVE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Custom Variable success save message is correct.';
    }
}
