<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that invoice is present in the invoices tab of the order with corresponding amount(Grand Total)
 */
class AssertInvoiceInInvoicesTab extends AbstractConstraint
{
    /**
     * Assert that invoice is present in the invoices tab of the order with corresponding amount(Grand Total)
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $orderIndex
     * @param OrderInjectable $order
     * @param array $ids
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $orderIndex,
        OrderInjectable $order,
        array $ids
    ) {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $salesOrderView->getOrderForm()->openTab('invoices');
        /** @var Grid $grid */
        $grid = $salesOrderView->getOrderInvoiceGrid();
        $amount = $order->getPrice();
        foreach ($ids['invoiceIds'] as $key => $invoiceId) {
            $filter = [
                'id' => $invoiceId,
                'grand_total_from' => $amount[$key]['grand_invoice_total'],
                'grand_total_to' => $amount[$key]['grand_invoice_total'],
            ];
            $grid->search($filter);
            $filter['amount_from'] = number_format($amount[$key]['grand_invoice_total'], 2);
            unset($filter['amount_to']);
            \PHPUnit_Framework_Assert::assertTrue(
                $grid->isRowVisible($filter, false, false),
                'Invoice is absent on invoices tab.'
            );
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Invoice is present on invoices tab.';
    }
}
