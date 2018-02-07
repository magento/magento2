<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\CreditMemos\Grid;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that refund is present in the tab with ID and refunded amount(depending on full/partial refund).
 */
class AssertRefundInCreditMemoTab extends AbstractConstraint
{
    /**
     * Assert that refund is present in the tab with ID and refunded amount(depending on full/partial refund).
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
        $salesOrderView->getOrderForm()->openTab('creditmemos');
        /** @var Grid $grid */
        $grid = $salesOrderView->getOrderForm()->getTab('creditmemos')->getGridBlock();
        $amount = $order->getPrice();
        foreach ($ids['creditMemoIds'] as $key => $creditMemoId) {
            $filter = [
                'id' => $creditMemoId,
                'amount_from' => $amount[$key]['grand_creditmemo_total'],
                'amount_to' => $amount[$key]['grand_creditmemo_total']
            ];
            \PHPUnit_Framework_Assert::assertTrue(
                $grid->isRowVisible($filter, true, false),
                'Credit memo is absent on credit memos tab.'
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
        return 'Credit memo is present on credit memos tab.';
    }
}
