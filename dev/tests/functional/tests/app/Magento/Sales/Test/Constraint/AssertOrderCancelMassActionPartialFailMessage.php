<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderCancelAndSuccessMassActionFailMessage
 * Assert cancel fail message is displayed on order index page
 */
class AssertOrderCancelMassActionPartialFailMessage extends AbstractConstraint
{
    /**
     * Message displayed after cancel order from archive
     */
    const SUCCESS_MESSAGE = 'We canceled 1 order(s).';

    /**
     * Text value to be checked
     */
    const FAIL_CANCEL_MESSAGE = '1 order(s) cannot be canceled.';

    /**
     * Assert cancel fail message is displayed on order index page
     *
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function processAssert(OrderIndex $orderIndex)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::FAIL_CANCEL_MESSAGE,
            $orderIndex->getMessagesBlock()->getErrorMessage()
        );
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $orderIndex->getMessagesBlock()->getSuccessMessage()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Cancel and success fail message is displayed on order index page.';
    }
}
