<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that comment about authorized amount exist in Comments History section on order page in backend.
 */
class AssertOrderCommentsHistory extends AbstractConstraint
{
    /**
     * Message about authorized amount in order.
     */
    const AUTHORIZED_AMOUNT = 'Authorized amount of $';

    /**
     * Assert  that comment about authorized amount exist in Comments History section on order page in backend.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param array $prices
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        array $prices
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        $actualAuthorizedAmount = $salesOrderView->getOrderHistoryBlock()->getCommentsHistory();

        \PHPUnit_Framework_Assert::assertContains(
            self::AUTHORIZED_AMOUNT . $prices['grandTotal'],
            $actualAuthorizedAmount,
            'Incorrect authorized amount value for the order #' . $orderId
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about authorized amount is available in Comments History section.";
    }
}
