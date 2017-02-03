<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderOnHoldFailMessage
 * Assert on hold fail message is displayed on order index page
 */
class AssertOrderOnHoldFailMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const FAIL_ON_HOLD_MESSAGE = 'No order(s) were put on hold.';

    /**
     * Assert on hold fail message is displayed on order index page
     *
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function processAssert(OrderIndex $orderIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::FAIL_ON_HOLD_MESSAGE,
            $orderIndex->getMessagesBlock()->getErrorMessage()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'On hold fail message is displayed on order index page.';
    }
}
