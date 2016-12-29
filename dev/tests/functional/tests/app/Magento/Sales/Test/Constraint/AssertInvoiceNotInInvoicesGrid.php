<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\InvoiceIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that invoice with corresponding order ID is absent in the invoices grid.
 */
class AssertInvoiceNotInInvoicesGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that invoice with corresponding order ID is absent in the invoices grid.
     *
     * @param InvoiceIndex $invoiceIndex
     * @param OrderInjectable $order
     * @param array $ids
     * @return void
     */
    public function processAssert(InvoiceIndex $invoiceIndex, OrderInjectable $order, array $ids)
    {
        $invoiceIndex->open();
        $amount = $order->getPrice();
        $orderId = $order->getId();
        foreach ($ids['invoiceIds'] as $key => $invoiceId) {
            $filter = [
                'id' => $invoiceId,
                'order_id' => $orderId,
                'grand_total_from' => $amount[$key]['grand_invoice_total'],
                'grand_total_to' => $amount[$key]['grand_invoice_total'],
            ];
            $invoiceIndex->getInvoicesGrid()->search($filter);
            $filter['grand_total_from'] = number_format($amount[$key]['grand_invoice_total'], 2);
            $filter['grand_total_to'] = number_format($amount[$key]['grand_invoice_total'], 2);
            \PHPUnit_Framework_Assert::assertFalse(
                $invoiceIndex->getInvoicesGrid()->isRowVisible($filter, false, false),
                'Invoice is present in invoices grid on invoice index page.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Invoice is absent in the invoices grid on invoice index page.';
    }
}
