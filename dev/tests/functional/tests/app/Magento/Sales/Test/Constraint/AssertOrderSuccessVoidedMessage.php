<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderStatusIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that success message about order void is present.
 */
class AssertOrderSuccessVoidedMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Message about successful void.
     */
    const SUCCESS_MESSAGE = 'The payment has been voided.';

    /**
     * Assert that success message is displayed after order is voided.
     *
     * @param OrderStatusIndex $orderStatusIndexPage
     * @return void
     */
    public function processAssert(OrderStatusIndex $orderStatusIndexPage)
    {
        $actualMessage = $orderStatusIndexPage->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text of voided order message assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order successful void message is present.';
    }
}
