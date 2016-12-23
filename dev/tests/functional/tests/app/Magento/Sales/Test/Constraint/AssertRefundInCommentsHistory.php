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
 * Assert that comment about refunded amount exists in Comments History section on order page in Admin.
 */
class AssertRefundInCommentsHistory extends AbstractConstraint
{
    /**
     * Pattern of message about refunded amount in order.
     */
    const REFUNDED_AMOUNT_PATTERN = '/^We refunded \w*\W{1,2}%s online. Transaction ID: "[\w\-]*"/';

    /**
     * Assert that comment about refunded amount exist in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param array $refundedPrices
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        array $refundedPrices
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        $actualRefundedAmount = $salesOrderView->getOrderHistoryBlock()->getRefundedAmount();
        foreach ($refundedPrices as $key => $refundedPrice) {
            \PHPUnit_Framework_Assert::assertRegExp(
                sprintf(self::REFUNDED_AMOUNT_PATTERN, $refundedPrice),
                $actualRefundedAmount[$key],
                'Incorrect refunded amount value for the order #' . $orderId
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
        return "Message about refunded amount is available in Comments History section.";
    }
}
