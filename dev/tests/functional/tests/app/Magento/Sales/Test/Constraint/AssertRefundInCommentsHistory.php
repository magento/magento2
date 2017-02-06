<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that comment about refunded amount exists in Comments History section on order page in Admin.
 */
class AssertRefundInCommentsHistory extends AbstractConstraint
{
    /**
     * Pattern of message about refunded amount in order.
     */
    const REFUNDED_AMOUNT_PATTERN = '/^We refunded \w*\W{1,2}%s online. Transaction ID: "[\w\-]*"/';

    /**
     * Assert that comment about refunded amount exists in Comments History section on order page in Admin.
     *
     * @param OrderInjectable $order
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $comments = $infoTab->getCommentsHistoryBlock()->getComments();

        foreach ($comments as $key => $comment) {
            if (stristr($comment['comment'], 'refunded') === false) {
                unset($comments[$key]);
            }
        }
        $comments = array_reverse(array_values($comments));

        $refundedPrices = $order->getPrice()['refund'];
        foreach ($refundedPrices as $key => $refundedPrice) {
            \PHPUnit_Framework_Assert::assertRegExp(
                sprintf(self::REFUNDED_AMOUNT_PATTERN, $refundedPrice['grand_creditmemo_total']),
                $comments[$key]['comment'],
                'Incorrect refunded amount value for the order #' . $orderId
            );
        }
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about refunded amount is available in Comments History section.";
    }
}
