<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderReleaseSuccessMessage
 * Assert release success message is displayed on order index page
 */
class AssertOrderReleaseSuccessMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const SUCCESS_RELEASE_MESSAGE = '%d order(s) have been released from on hold status.';

    /**
     * Assert release success message is displayed on order index page
     *
     * @param OrderIndex $orderIndex
     * @param int $ordersCount
     * @return void
     */
    public function processAssert(OrderIndex $orderIndex, $ordersCount)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_RELEASE_MESSAGE, $ordersCount),
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
        return 'Release success message is displayed on order index page.';
    }
}
