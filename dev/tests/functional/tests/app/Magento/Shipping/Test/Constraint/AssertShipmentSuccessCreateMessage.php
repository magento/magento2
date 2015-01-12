<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertShipmentSuccessCreateMessage
 * Assert that success message is displayed after shipment has been created
 */
class AssertShipmentSuccessCreateMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Shipment created success message
     */
    const SUCCESS_MESSAGE = 'The shipment has been created.';

    /**
     * Assert success message presents
     *
     * @param OrderView $orderView
     * @return void
     */
    public function processAssert(OrderView $orderView)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
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
        return 'Shipment success create message is present.';
    }
}
