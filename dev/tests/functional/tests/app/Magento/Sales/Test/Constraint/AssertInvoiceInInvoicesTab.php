<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertInvoiceInInvoicesTab
 * Assert that invoice is present in the invoices tab of the order with corresponding amount(Grand Total)
 */
class AssertInvoiceInInvoicesTab extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that invoice is present in the invoices tab of the order with corresponding amount(Grand Total)
     *
     * @param OrderView $orderView
     * @param OrderIndex $orderIndex
     * @param OrderInjectable $order
     * @param array $ids
     * @return void
     */
    public function processAssert(
        OrderView $orderView,
        OrderIndex $orderIndex,
        OrderInjectable $order,
        array $ids
    ) {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $orderView->getOrderForm()->openTab('invoices');
        /** @var Grid $grid */
        $grid = $orderView->getOrderForm()->getTabElement('invoices')->getGridBlock();
        $amount = $order->getPrice();
        foreach ($ids['invoiceIds'] as $key => $invoiceId) {
            $filter = [
                'id' => $invoiceId,
                'amount_from' => $amount[$key]['grand_invoice_total'],
                'amount_to' => $amount[$key]['grand_invoice_total'],
            ];
            $grid->search($filter);
            $filter['amount_from'] = number_format($amount[$key]['grand_invoice_total'], 2);
            $filter['amount_to'] = number_format($amount[$key]['grand_invoice_total'], 2);
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
