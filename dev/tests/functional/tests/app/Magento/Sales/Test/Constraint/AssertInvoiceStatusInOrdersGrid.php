<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert invoice status on order page in Admin.
 */
class AssertInvoiceStatusInOrdersGrid extends AbstractConstraint
{
    /**
     * Assert invoice status on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param string $invoiceStatus
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        $invoiceStatus,
        $orderId
    ) {
        $salesOrderView->open(['order_id' => $orderId]);
        $salesOrderView->getOrderForm()->openTab('invoices');
        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid $grid */
        $grid = $salesOrderView->getOrderForm()->getTab('invoices')->getGridBlock();
        $filter = [
            'order_id' => $orderId,
            'status' => $invoiceStatus,
        ];
        \PHPUnit\Framework\Assert::assertTrue(
            $grid->isRowVisible($filter),
            'Invoice status is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Invoice status is correct.';
    }
}
