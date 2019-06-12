<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Assert that comment about canceled amount exists in
 * Comments History section on order page in Admin.
 */
class AssertCancelInCommentsHistory extends AbstractConstraint
{
    /**
     * Pattern of message about canceled amount in order.
     */
    private $canceledAmountPattern = '/^Canceled order online Amount: \w*\W{1,2}%s. Transaction ID: "[\w\-]*"/';

    /**
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param array $prices
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        array $prices
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $comments = $infoTab->getCommentsHistoryBlock()->getComments();
        $commentsMessages = array_column($comments, 'comment');

        \PHPUnit\Framework\Assert::assertRegExp(
            sprintf($this->canceledAmountPattern, $prices['grandTotal']),
            implode('. ', $commentsMessages),
            'Incorrect canceled amount value for the order #' . $orderId
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about canceled amount is available in Comments History section.";
    }
}
