<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that status is Canceled.
 */
class AssertOrderStatusIsCanceled extends AbstractConstraint
{
    /**
     * Assert that status is Canceled.
     *
     * @param OrderIndex $salesOrder
     * @param SalesOrderView $salesOrderView
     * @return void
     */
    public function processAssert(
        OrderIndex $salesOrder,
        SalesOrderView $salesOrderView
    ) {
        $salesOrder->open();
        $grid = $salesOrder->getSalesOrderGrid();
        $grid->resetFilter();
        $grid->sortByColumn('ID');
        $grid->sortGridByField('ID');
        $grid->openFirstRow();

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        \PHPUnit_Framework_Assert::assertEquals(
            $infoTab->getOrderStatus(),
            'Canceled'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order status is correct.';
    }
}
