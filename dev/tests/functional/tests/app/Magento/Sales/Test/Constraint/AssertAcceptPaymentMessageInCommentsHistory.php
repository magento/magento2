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
 * Assert that accept payment message exists in Comments History section on order page in Admin.
 *
 */
class AssertAcceptPaymentMessageInCommentsHistory extends AbstractConstraint
{
    /**
     * Accept payment message.
     *
     * @var string
     */
    private static $message = 'Approved the payment online.';

    /**
     * Assert that accept payment message exists in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $orderIndex
     * @param $orderId
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView, OrderIndex $orderIndex, $orderId)
    {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $latestComment = $infoTab->getCommentsHistoryBlock()->getLatestComment();

        \PHPUnit_Framework_Assert::assertContains(self::$message, $latestComment['comment']);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Message about approved payment is available in Comments History section.';
    }
}
