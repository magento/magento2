<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderStatusNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderStatusDuplicateStatus
 *
 */
class AssertOrderStatusDuplicateStatus extends AbstractConstraint
{
    const DUPLICATE_MESSAGE = 'We found another order status with the same order status code.';

    /**
     * Assert that duplicate message is displayed
     *
     * @param OrderStatusNew $orderStatusNewPage
     * @return void
     */
    public function processAssert(OrderStatusNew $orderStatusNewPage)
    {
        $actualMessage = $orderStatusNewPage->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::DUPLICATE_MESSAGE,
            $actualMessage,
            'Wrong duplicate message is displayed.'
            . "\nExpected: " . self::DUPLICATE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text of Duplicate Message assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order status duplicate message is present.';
    }
}
