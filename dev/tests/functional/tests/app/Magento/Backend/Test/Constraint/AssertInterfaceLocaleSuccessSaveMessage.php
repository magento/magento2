<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\SystemAccount;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assertion to check Success Save Message when Interface Locale is changed.
 */
class AssertInterfaceLocaleSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'You saved the account.';

    /**
     * Assert save message after switching interface locale.
     *
     * @param SystemAccount $systemAccount
     * @return void
     */
    public function processAssert(SystemAccount $systemAccount)
    {
        $actualMessage = $systemAccount->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
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
        return 'Asserts that success message is displayed when switching interface locale.';
    }
}
