<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\InvoiceIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesInvoiceView;

/**
 * Class AssertInvoiceItems
 * Assert invoice items on invoice view page
 */
class AssertInvoiceItems extends AbstractAssertItems
{
    /**
     * Assert invoice items on invoice view page
     *
     * @param InvoiceIndex $invoiceIndex
     * @param SalesInvoiceView $salesInvoiceView
     * @param OrderInjectable $order
     * @param array $ids
     * @param array|null $data [optional]
     * @return void
     */
    public function processAssert(
        InvoiceIndex $invoiceIndex,
        SalesInvoiceView $salesInvoiceView,
        OrderInjectable $order,
        array $ids,
        array $data = null
    ) {
        $invoiceIndex->open();
        $orderId = $order->getId();
        $productsData = $this->prepareOrderProducts($order, $data['items_data']);
        foreach ($ids['invoiceIds'] as $invoiceId) {
            $filter = [
                'order_id' => $orderId,
                'id' => $invoiceId,
            ];
            $invoiceIndex->getInvoicesGrid()->searchAndOpen($filter);
            $itemsData = $this->preparePageItems($salesInvoiceView->getItemsBlock()->getData());
            $error = $this->verifyData($productsData, $itemsData);
            \PHPUnit_Framework_Assert::assertEmpty($error, $error);
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'All invoice products are present in invoice view page.';
    }
}
