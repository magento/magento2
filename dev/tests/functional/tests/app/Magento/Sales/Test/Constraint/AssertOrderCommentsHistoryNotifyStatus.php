<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Assert that comment has appropriate notification status in Comments History section on order page in Admin.
 */
class AssertOrderCommentsHistoryNotifyStatus extends AbstractConstraint
{
    /**
     * Assert that comment has appropriate notification status in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param OrderInjectable $order
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        OrderInjectable $order
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $refundsData = $order->getRefund();
        $sendMail = isset($refundsData[0]['form_data']['send_email'])
            ? filter_var($refundsData[0]['form_data']['send_email'], FILTER_VALIDATE_BOOLEAN)
            : false;
        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $latestComment = $infoTab->getCommentsHistoryBlock()->getLatestComment();

        \PHPUnit\Framework\Assert::assertContains(
            $latestComment['is_customer_notified'],
            (bool)$sendMail ? 'Customer Notified' : 'Customer Not Notified'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message with appropriate notification status is available in Comments History section.";
    }
}
