<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderStatusNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderStatusDuplicateStatus
 *
 */
class AssertOrderStatusDuplicateStatus extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    const DUPLICATE_MESSAGE = 'We found another order status with the same order status code.';

    /**
     * Assert that duplicate message is displayed
     *
     * @param OrderStatusNew $orderStatusNewPage
     * @return void
     */
    public function processAssert(OrderStatusNew $orderStatusNewPage)
    {
        $actualMessage = $orderStatusNewPage->getMessagesBlock()->getErrorMessages();
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
