<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderOnHoldSuccessMessage
 * Assert on hold success message is displayed on order view page
 */
class AssertOrderOnHoldSuccessMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Text value to be checked for single order on hold
     */
    const SINGLE_SUCCESS_ON_HOLD_MESSAGE = 'You put the order on hold.';

    /**
     * Text value to be checked for multiple order on hold
     */
    const MULTIPLE_SUCCESS_ON_HOLD_MESSAGE = 'You have put %d order(s) on hold.';

    /**
     * Assert on hold success message is displayed on order index page
     *
     * @param OrderIndex $orderIndex
     * @param int $ordersCount
     * @return void
     */
    public function processAssert(OrderIndex $orderIndex, $ordersCount = null)
    {
        $successOnHoldMessage = ($ordersCount > 1)
            ? sprintf(self::MULTIPLE_SUCCESS_ON_HOLD_MESSAGE, $ordersCount)
            : self::SINGLE_SUCCESS_ON_HOLD_MESSAGE;

        \PHPUnit_Framework_Assert::assertEquals(
            $successOnHoldMessage,
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
        return 'On hold success message is displayed on order view page.';
    }
}
