<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderCancelSuccessMessage
 * Assert that after cancel sales order successful message appears
 */
class AssertOrderCancelSuccessMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Message displayed after cancel sales order
     */
    const SUCCESS_CANCEL_MESSAGE = 'You canceled the order.';

    /**
     * Assert that after cancel sales order successful message appears
     *
     * @param OrderView $orderView
     * @return void
     */
    public function processAssert(OrderView $orderView)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_CANCEL_MESSAGE,
            $orderView->getMessagesBlock()->getSuccessMessages()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales order success cancel message is present.';
    }
}
