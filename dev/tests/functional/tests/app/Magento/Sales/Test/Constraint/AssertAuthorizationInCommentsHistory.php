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
 * Assert that comment about authorized amount exists in Comments History section on order page in Admin.
 */
class AssertAuthorizationInCommentsHistory extends AbstractConstraint
{
    /**
     * Pattern of message about authorized amount in order.
     */
    const AUTHORIZED_AMOUNT_PATTERN = '/(IPN "Pending" )*Authorized amount of .+?%s. Transaction ID: "[\w\-]*"/';

    /**
     * Assert that comment about authorized amount exists in Comments History section on order page in Admin.
     *
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
        $orderComments = $infoTab->getCommentsHistoryBlock()->getComments();
        $commentsMessages = array_column($orderComments, 'comment');

        \PHPUnit_Framework_Assert::assertRegExp(
            sprintf(self::AUTHORIZED_AMOUNT_PATTERN, $prices['grandTotal']),
            implode('. ', $commentsMessages),
            'Incorrect authorized amount value for the order #' . $orderId
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about authorized amount is available in Comments History section.";
    }
}
