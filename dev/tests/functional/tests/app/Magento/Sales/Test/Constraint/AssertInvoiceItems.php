<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\InvoiceIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesInvoiceView;

/**
 * Assert invoice items on invoice view page.
 */
class AssertInvoiceItems extends AbstractAssertItems
{
    /**
     * Assert invoice items on invoice view page.
     *
     * @param InvoiceIndex $invoiceIndex
     * @param SalesInvoiceView $salesInvoiceView
     * @param OrderInjectable $order
     * @param array $ids
     * @param Cart|null $cart [optional]
     * @return void
     */
    public function processAssert(
        InvoiceIndex $invoiceIndex,
        SalesInvoiceView $salesInvoiceView,
        OrderInjectable $order,
        array $ids,
        Cart $cart = null
    ) {
        $orderId = $order->getId();
        $invoicesData = $order->getInvoice();
        $data = isset($invoicesData[0]['items_data']) ? $invoicesData[0]['items_data'] : [];
        $productsData = $this->prepareOrderProducts($order, $data, $cart);
        foreach ($ids['invoiceIds'] as $invoiceId) {
            $filter = [
                'order_id' => $orderId,
                'id' => $invoiceId,
            ];
            $invoiceIndex->open();
            $invoiceIndex->getInvoicesGrid()->searchAndOpen($filter);
            $itemsData = $this->preparePageItems($salesInvoiceView->getItemsBlock()->getData());
            $error = $this->verifyData($productsData, $itemsData);
            \PHPUnit\Framework\Assert::assertEmpty($error, $error);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'All invoice products are present in invoice view page.';
    }
}
