<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\CreditMemos\Grid;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertRefundInCreditMemoTab
 * Assert that refund is present in the tab with ID and refunded amount(depending on full/partial refund)
 */
class AssertRefundInCreditMemoTab extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that refund is present in the tab with ID and refunded amount(depending on full/partial refund)
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
        $orderView->getOrderForm()->openTab('creditmemos');
        /** @var Grid $grid */
        $grid = $orderView->getOrderForm()->getTabElement('creditmemos')->getGridBlock();
        $amount = $order->getPrice();
        foreach ($ids['creditMemoIds'] as $key => $creditMemoId) {
            $filter = [
                'id' => $creditMemoId,
                'amount_from' => $amount[$key]['grand_creditmemo_total'],
                'amount_to' => $amount[$key]['grand_creditmemo_total'],
            ];
            $grid->search($filter);
            $filter['amount_from'] = number_format($amount[$key]['grand_creditmemo_total'], 2);
            $filter['amount_to'] = number_format($amount[$key]['grand_creditmemo_total'], 2);
            \PHPUnit_Framework_Assert::assertTrue(
                $grid->isRowVisible($filter, false, false),
                'Credit memo is absent on credit memos tab.'
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
        return 'Credit memo is present on credit memos tab.';
    }
}
