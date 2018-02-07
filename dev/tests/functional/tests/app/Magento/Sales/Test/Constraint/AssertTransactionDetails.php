<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Transactions\Grid;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that transaction is present in the Transactions tab of the order with corresponding status
 */
class AssertTransactionDetails extends AbstractConstraint
{
    /**
     * Message about authorized amount in order.
     */
    const AUTHORIZED_AMOUNT = 'Authorized amount of $';

    /**
     * Assert that comment about authorized amount exist in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param array $transactionDetails
     * @throws \Exception
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        array $transactionDetails
    ) {
        $transactionId = '';
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $comment = $salesOrderView->getOrderHistoryBlock()->getCommentsHistory();
        preg_match('/(ID: ")(\w+-*\w+)(")/', $comment, $matches);
        if (!empty($matches[2])) {
            $transactionId = $matches[2];
        }
        \PHPUnit_Framework_Assert::assertNotEmpty($transactionId);
        $orderForm = $salesOrderView->getOrderForm()->openTab('transactions');
        /** @var Grid $grid */
        $grid = $orderForm->getTab('transactions')->getGridBlock();
        $actualTxnIds = $grid->getIds();
        \PHPUnit_Framework_Assert::assertEquals(
            $transactionDetails,
            $actualTxnIds[$transactionId],
            'Incorrect transaction details for the order #' . $orderId
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about transaction details are present in Transactions tab.";
    }
}
