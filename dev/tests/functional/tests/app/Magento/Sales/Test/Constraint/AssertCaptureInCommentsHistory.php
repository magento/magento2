<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that comment about captured amount exists in Comments History section on order page in Admin.
 */
class AssertCaptureInCommentsHistory extends AbstractConstraint
{
    /**
     * Message about captured amount in order.
     */
    const CAPTURED_AMOUNT = 'Captured amount of $';

    /**
     * Assert that comment about captured amount exist in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param array $capturedPrices
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        array $capturedPrices
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        $actualCapturedAmount = $salesOrderView->getOrderHistoryBlock()->getCapturedAmount();
        foreach ($capturedPrices as $key => $capturedPrice) {
            \PHPUnit_Framework_Assert::assertContains(
                self::CAPTURED_AMOUNT . $capturedPrice,
                $actualCapturedAmount[$key],
                'Incorrect captured amount value for the order #' . $orderId
            );
        }
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about captured amount is available in Comments History section.";
    }
}
