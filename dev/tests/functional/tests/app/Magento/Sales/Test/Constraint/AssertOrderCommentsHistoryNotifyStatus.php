<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Class AssertOrderCommentsHistoryNotifyStatus
 */
class AssertOrderCommentsHistoryNotifyStatus extends AbstractConstraint
{
    /**
     * Assert that comment about refunded amount exist in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param OrderInjectable $order
     * @param array $data
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        OrderInjectable $order,
        array $data
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $sendMail = isset($data['form_data']['send_email']) ? filter_var(
            $data['form_data']['send_email'],
            FILTER_VALIDATE_BOOLEAN
        ) : false;

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $latestComment = $infoTab->getCommentHistoryBlock()->getLatestComment();

        \PHPUnit_Framework_Assert::assertContains(
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
