<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that comment about created Signifyd Case
 * exists in Comments History section on order page in Admin.
 */
class AssertSignifydCaseInCommentsHistory extends AbstractConstraint
{
    /**
     * Pattern of message about created Signifyd Case in order.
     */
    const CASE_CREATED_PATTERN = '/Signifyd Case (\d)+ has been created for order\./';

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

        \PHPUnit\Framework\Assert::assertRegExp(
            self::CASE_CREATED_PATTERN,
            implode('. ', $commentsMessages),
            'Signifyd case is not created for the order #' . $orderId
        );
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return "Message about Signifyd Case is available in Comments History section.";
    }
}
