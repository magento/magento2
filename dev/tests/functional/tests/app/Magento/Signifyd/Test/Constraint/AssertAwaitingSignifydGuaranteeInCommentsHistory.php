<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Assert that comment about awaiting the Signifyd guarantee disposition
 * exists in Comments History section on order page in Admin.
 */
class AssertAwaitingSignifydGuaranteeInCommentsHistory extends AbstractConstraint
{
    /**
     * Expected history comment.
     */
    private $historyComment = 'Awaiting the Signifyd guarantee disposition.';

    /**
     * Expected history status.
     */
    private $historyCommentStatus = 'On Hold';

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

        $key = array_search(
            $this->historyComment,
            array_column($orderComments, 'comment')
        );

<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertNotFalse(
=======
        \PHPUnit\Framework\Assert::assertNotFalse(
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $key,
            'There is no message about awaiting the Signifyd guarantee disposition' .
            ' in Comments History section for the order #' . $orderId
        );

<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertEquals(
=======
        \PHPUnit\Framework\Assert::assertEquals(
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $this->historyCommentStatus,
            $orderComments[$key]['status'],
            'Message about awaiting the Signifyd guarantee disposition' .
            ' doesn\'t have status "'. $this->historyCommentStatus.'"' .
            ' in Comments History section for the order #' . $orderId
        );
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return "Message about awaiting the Signifyd guarantee disposition is available in Comments History section.";
    }
}
