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
 * Assert that comment about captured amount exists in Comments History section on order page in Admin.
 */
class AssertCaptureInCommentsHistory extends AbstractConstraint
{
    /**
     * Pattern of message about captured amount in order.
     */
    const CAPTURED_AMOUNT_PATTERN = '/^Captured amount of .+?%s online\. Transaction ID: "[\w\-]*"/';

    /**
     * Assert that comment about captured amount exists in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param OrderInjectable $order
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        OrderInjectable $order,
        $orderId
    ) {
        $capturedPrices = $order->getPrice()['captured_prices'];
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $comments = $infoTab->getCommentsHistoryBlock()->getComments();

        foreach ($comments as $key => $comment) {
            if (strstr($comment['comment'], 'Captured') === false) {
                unset($comments[$key]);
            }
        }
        $comments = array_values($comments);

        foreach ($capturedPrices as $key => $capturedPrice) {
            \PHPUnit\Framework\Assert::assertRegExp(
                sprintf(self::CAPTURED_AMOUNT_PATTERN, preg_quote(number_format($capturedPrice, 2, '.', ''))),
                $comments[$key]['comment'],
                'Incorrect captured amount value for the order #' . $orderId
            );
        }
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about captured amount is available in Comments History section.";
    }
}
