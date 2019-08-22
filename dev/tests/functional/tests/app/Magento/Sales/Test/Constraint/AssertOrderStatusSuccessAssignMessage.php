<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderStatusIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderStatusSuccessAssignMessage
 * Assert that after assigning order status success message appears
 */
class AssertOrderStatusSuccessAssignMessage extends AbstractConstraint
{
    /**
     * OrderStatus assigning success message
     */
    const SUCCESS_MESSAGE = 'You assigned the order status.';

    /**
     * Assert that success message is displayed after order status assigning
     *
     * @param OrderStatusIndex $orderStatusIndexPage
     * @return void
     */
    public function processAssert(OrderStatusIndex $orderStatusIndexPage)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $orderStatusIndexPage->getMessagesBlock()->getSuccessMessage()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Order status success assign message is present.';
    }
}
