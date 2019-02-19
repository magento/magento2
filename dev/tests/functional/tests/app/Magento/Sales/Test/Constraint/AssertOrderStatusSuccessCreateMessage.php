<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderStatusIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderStatusSuccessCreateMessage
 *
 */
class AssertOrderStatusSuccessCreateMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_MESSAGE = 'You saved the order status.';

    /**
     * Assert that success message is displayed after order status saved.
     *
     * @param OrderStatusIndex $orderStatusIndexPage
     * @return void
     */
    public function processAssert(OrderStatusIndex $orderStatusIndexPage)
    {
        $actualMessage = $orderStatusIndexPage->getMessagesBlock()->getSuccessMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text of Created Custom Order Status Success Message assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order status success create message is present.';
    }
}
