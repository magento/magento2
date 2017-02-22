<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Items Ordered section on Create Order page on backend is empty.
 */
class AssertItemsOrderedSectionOnBackendOrderIsEmpty extends AbstractConstraint
{
    /**
     * "No ordered items" message on Create Order page on backend.
     */
    const TEXT_MESSAGE = 'No ordered items';

    /**
     * Assert that Items Ordered section on Create Order page on backend is empty.
     *
     * @param OrderCreateIndex $orderCreateIndex
     * @return void
     */
    public function processAssert(OrderCreateIndex $orderCreateIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $orderCreateIndex->getCreateBlock()->getItemsBlock()->getEmptyTextMessage(),
            self::TEXT_MESSAGE,
            'Items Ordered section on Create Order page on backend is not empty.'
        );
    }

    /**
     * Assert success message that Items Ordered section on Create Order page on backend is empty.
     *
     * @return string
     */
    public function toString()
    {
        return 'Items Ordered section on Create Order page on backend is empty.';
    }
}
