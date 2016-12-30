<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that comment about captured amount exists in Comments History section on order page in Admin.
 */
class AssertCaptureInCommentsHistory extends AbstractConstraint
{
    /**
     * Pattern of message about captured amount in order.
     */
    const CAPTURED_AMOUNT_PATTERN = '/^Captured amount of \w*\W{1,2}%s online. Transaction ID: "[\w\-]*"/';

    /**
     * Assert that comment about captured amount exists in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param array $capturedPrices
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        array $capturedPrices
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $comments = $infoTab->getCommentsHistoryBlock()->getComments();

        foreach ($comments as $key => $comment) {
            if (stristr($comment['comment'], 'captured') === false) {
                unset($comments[$key]);
            }
        }

        foreach ($capturedPrices as $key => $capturedPrice) {
            \PHPUnit_Framework_Assert::assertRegExp(
                sprintf(self::CAPTURED_AMOUNT_PATTERN, $capturedPrice),
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
