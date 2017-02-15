<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

class AssertGuaranteeCancelInCommentsHistory extends AbstractConstraint
{
    /**
     * Pattern of message about canceled amount in order.
     */
    private $guaranteeCancelPattern = 'Case Update: Case guarantee has been cancelled.';

    /**
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @return void
     */
    public function processAssert(
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
            if (strstr($comment['comment'], 'Case Update') === false) {
                unset($comments[$key]);
            }
        }
        $comments = array_values($comments);

        \PHPUnit_Framework_Assert::assertRegExp(
            $this->guaranteeCancelPattern,
            $comments[0]['comment'],
            'Incorrect guarantee cancel for the order #' . $orderId
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about guarantee cancel is available in Comments History section.";
    }
}
