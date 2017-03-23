<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderCancelMassActionSuccessMessage
 * Assert cancel success message is displayed on order index page
 */
class AssertOrderCancelMassActionSuccessMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const SUCCESS_CANCEL_MESSAGE = 'We canceled %d order(s).';

    /**
     * Assert cancel success message is displayed on order index page
     *
     * @param OrderIndex $orderIndex
     * @param int $ordersCount
     * @return void
     */
    public function processAssert(OrderIndex $orderIndex, $ordersCount)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_CANCEL_MESSAGE, $ordersCount),
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
        return 'Cancel success message is displayed on order index page.';
    }
}
