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
     * @param SalesOrderView $orderView
     * @param OrderIndex $orderIndex
     * @param $orderId
     */
    public function processAssert(SalesOrderView $orderView, OrderIndex $orderIndex, $orderId)
    {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $history = $orderView->getOrderHistoryBlock()->getCommentsHistory();

        \PHPUnit_Framework_Assert::assertContains(self::$message, $history);
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Message about approved payment is available in Comments History section.';
    }
}
