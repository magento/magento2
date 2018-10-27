<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Assert that comment about created Signifyd Case guarantee
 * has been cancelled exists in Comments History section on order page in Admin.
 */
class AssertSignifydGuaranteeCancelInCommentsHistory extends AbstractConstraint
{
    /**
     * Signifyd case guarantee cancel message in order view.
     */
    private $guaranteeCancelMessage = 'Case Update: Case guarantee has been cancelled.';

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
        $orderComments = $infoTab->getCommentsHistoryBlock()->getComments();
        $commentsMessages = array_column($orderComments, 'comment');

        \PHPUnit_Framework_Assert::assertContains(
            $this->guaranteeCancelMessage,
            implode('. ', $commentsMessages),
            'There is no message regarding Signifyd guarantee cancel in Comments History section for the order #'
            . $orderId
        );
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return "Message about Signifyd guarantee cancel is available in Comments History section.";
    }
}
