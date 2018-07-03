<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that transactions status is closed on order page in Admin.
 */
class AssertTransactionStatus extends AbstractConstraint
{
    /**
     * Assert that transactions status is closed on order page in Admin.
     *
     * @param OrderIndex $salesOrder
     * @param SalesOrderView $salesOrderView
     * @param array $transactions
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        OrderIndex $salesOrder,
        SalesOrderView $salesOrderView,
        array $transactions,
        $orderId
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $salesOrderView->getOrderForm()->openTab('transactions');
        $actualTransactions = $salesOrderView->getOrderForm()->getTab('transactions')->getGridBlock()->getIds();

        foreach ($transactions as $transaction) {
            foreach ($actualTransactions as $actualTransaction) {
                if ($actualTransaction['transactionType'] === $transaction['transactionType']) {
                    \PHPUnit\Framework\Assert::assertEquals(
                        $transaction['statusIsClosed'],
                        $actualTransaction['statusIsClosed'],
                        'The ' . $transaction['transactionType'] . ' transaction status is not closed.'
                    );
                    break;
                }
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Transactions status is closed.';
    }
}
