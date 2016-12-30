<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Class AssertAcceptPaymentMessageInCommentsHistory
 *
 * Constraint checks accept payment message in order comments history
 */
class AssertAcceptPaymentMessageInCommentsHistory extends AbstractConstraint
{

    /**
     * @var string
     */
    private static $message = 'Approved the payment online.';

    /**
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $orderIndex
     * @param $orderId
     * @internal param SalesOrderView $orderView
     */
    public function processAssert(SalesOrderView $salesOrderView, OrderIndex $orderIndex, $orderId)
    {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $latestComment = $infoTab->getCommentHistoryBlock()->getLatestComment();

        \PHPUnit_Framework_Assert::assertContains(self::$message, $latestComment['comment']);
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Message about approved payment is available in Comments History section.';
    }
}
