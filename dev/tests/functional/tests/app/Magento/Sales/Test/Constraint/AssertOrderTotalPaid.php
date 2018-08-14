<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Assert that Order Total Paid is correct on order page in Admin.
 */
class AssertOrderTotalPaid extends AbstractConstraint
{
    /**
     * Assert that Order Total Paid is correct on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param array $prices
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        array $prices,
        $orderId
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        \PHPUnit_Framework_Assert::assertEquals(
            number_format($prices['totalPaid'], 2, '.', ','),
            $salesOrderView->getOrderTotalsBlock()->getTotalPaid(),
            'Total Paid price does not equal to price from data set.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Total Paid price equals to price from data set.';
    }
}
