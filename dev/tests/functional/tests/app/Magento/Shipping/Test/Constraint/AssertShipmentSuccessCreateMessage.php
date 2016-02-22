<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that success message is displayed after shipment has been created
 */
class AssertShipmentSuccessCreateMessage extends AbstractConstraint
{
    /**
     * Shipment created success message
     */
    const SUCCESS_MESSAGE = 'The shipment has been created.';

    /**
     * Assert success message presents
     *
     * @param SalesOrderView $salesOrderView
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $salesOrderView->getMessagesBlock()->getSuccessMessage()
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
