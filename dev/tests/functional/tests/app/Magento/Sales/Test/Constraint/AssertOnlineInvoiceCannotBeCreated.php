<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert message that invoice can be created only offline is present.
 */
class AssertOnlineInvoiceCannotBeCreated extends AbstractConstraint
{
    /**
     * Message that invoice can be created only offline.
     */
    const OFFLINE_INVOICE_MESSAGE = 'The invoice will be created offline without the payment gateway.';

    /**
     * Assert message that invoice can be created only offline is present.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param OrderInvoiceNew $orderInvoiceNew
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        OrderInvoiceNew $orderInvoiceNew,
        $orderId
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $salesOrderView->getPageActions()->invoice();

        \PHPUnit_Framework_Assert::assertEquals(
            self::OFFLINE_INVOICE_MESSAGE,
            $orderInvoiceNew->getTotalsBlock()->getCaptureOfflineMessage(),
            'Message incorrect or is not present.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message that invoice can be created only offline is present.";
    }
}
