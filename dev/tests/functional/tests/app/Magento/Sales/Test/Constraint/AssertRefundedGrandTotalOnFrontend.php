<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\CreditMemoView;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\CustomerOrderView;

/**
 * Assert that refunded grand total is equal to data from fixture on My Account page
 */
class AssertRefundedGrandTotalOnFrontend extends AbstractAssertOrderOnFrontend
{
    /**
     * Assert that refunded grand total is equal to data from fixture on My Account page
     *
     * @param OrderHistory $orderHistory
     * @param OrderInjectable $order
     * @param CustomerOrderView $customerOrderView
     * @param CreditMemoView $creditMemoView
     * @param array $ids
     * @return void
     */
    public function processAssert(
        OrderHistory $orderHistory,
        OrderInjectable $order,
        CustomerOrderView $customerOrderView,
        CreditMemoView $creditMemoView,
        array $ids
    ) {
        $this->loginCustomerAndOpenOrderPage($order->getDataFieldConfig('customer_id')['source']->getCustomer());
        $orderHistory->getOrderHistoryBlock()->openOrderById($order->getId());
        $customerOrderView->getOrderViewBlock()->openLinkByName('Refunds');
        foreach ($ids['creditMemoIds'] as $key => $creditMemoId) {
            \PHPUnit_Framework_Assert::assertEquals(
                number_format($order->getPrice()[$key]['grand_creditmemo_total'], 2),
                $creditMemoView->getCreditMemoBlock()->getItemBlock($creditMemoId)->getGrandTotal()
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
        return 'Credit memo Grand Total amount is equal to placed order Grand Total amount on credit memo page.';
    }
}
